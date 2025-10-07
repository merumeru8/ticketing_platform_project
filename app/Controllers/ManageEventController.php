<?php

namespace Controllers;

use DateTime;
use Error;
use Exception;
use Models\TicketModel;

class ManageEventController extends \Config\BaseController
{
    /**
     * Renders create/edit event form
     * @param int|null $tId   Ticket ID to edit
     * @return void
     */
    public function manageEvent($tId = null){
        
        $tModel = new TicketModel();

        $ticket = null;

        $data = [];

        // Default page title (overridden when editing)
        $data['page_title'] = "Create New Ticket";

        if($tId){
            // Fetch ticket only if owned by current user and not deleted
            $ticket = $tModel->getTicketIfOwner(getSession("user_id"), $tId, 0);

            // Forbidden if the user is not the creator or the ticket doesn't exist
            if(! $ticket || count($ticket) == 0){
                http_response_code(403);
                echo "403 Forbidden";
                exit;
            }

            // Switch to edit mode
            $data['page_title'] = "Edit Ticket";
            $ticket = $ticket[0];

            // Parse time boundaries
            $start = new DateTime($ticket['starts_at']);
            $end = new DateTime($ticket['ends_at']);

            $start = $start->format('Y-m-d');
            $end = $end->format('Y-m-d');
        }

        // Populate form fields with fallbacks for "create" mode
        $data['title'] = $ticket ? $ticket['title'] : "";
        $data['price'] = $ticket ? $ticket['price'] : 0;
        $data['max'] = $ticket ? $ticket['max_quantity'] : 0;
        $data['start'] = $ticket ? $start : "";
        $data['end'] = $ticket ? $end : "";
        $data['visibility'] = $ticket ? $ticket['visibility'] : 0;
        $data['buyers'] = $ticket ? $ticket['buyer_count'] : 0;
        $data['ticket_id'] = $tId;
        $data['image'] = $ticket ? $ticket['image'] : "";

        // If buyers exist, prevent making the event private (handled by view/UI)
        $data['no_private'] = $ticket ? $ticket['buyer_count'] > 0 : 0;

        // Render the view
        view("manage_event", $data);
    }

    /**
     * Handle create/update submission (Ajax endpoint)
     * 
     * @return void
     */
    public function saveEvent(){
        
        // Raw payload via multipart/form-data
        $payload = $_POST;

        // Collect and mark required fields
        $required = [];
        $title = $required[]= $payload['title'] ?? "";
        $max = $required[]= $payload['max'] ?? "";
        $price = $required[]= $payload['price'] ?? "";
        $ticketId = $payload['ticketId'] ?? null;
        $start = $required[]= $payload['start'] ?? "";
        $end = $required[]= $payload['end'] ?? "";
        $visibility = $required[]= isset($payload['visibility']) ? $payload['visibility'] : "";

        // Basic presence validation
        foreach($required as $r){
            if(is_null($r) || $r === ""){
                echo json_encode(['error' => 1, 'message' => "Please fill out all fields in the form.", 'status' => 200]);
                exit;
            }
        }

        // Optional image upload defaults
        $file_name = "";
        $ext = "";

        // Validate uploaded image (if any)
        if(isset($_FILES['file_img']) && isset($_FILES['file_img']['name'])){
            
            // Sanitize filename (prevent quotes/slashes)
            $file_name = str_replace(["'", "/", "\\"], "_", $_FILES['file_img']['name']);

            $mimeType = $_FILES["file_img"]['type'];

            // Extract extension
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Allow only PNG/JPEG (both by MIME and extension)
            if(! in_array(strtolower($mimeType), ["image/png", "image/jpeg"]) || ! in_array(strtolower($ext), ["png", "jpg", "jpeg"])){
                echo json_encode(['error' => 1, 'message' => "Please upload a valid image format. Only .png, .jpg, and .jpeg are allowed.", 'status' => 500]);    
                exit;
            }
        }

        $tModel = new TicketModel();

        try{
            if($ticketId){
                // EDIT MODE: only the owner of a non deleted ticket can edit
                $ticket = $tModel->getTicketIfOwner(getSession("user_id"), $ticketId, 0);

                //if ticket doesn't exists or it's deleted or it's not owned by the logged user
                if(! $ticket || count($ticket) == 0){
                    http_response_code(403);
                    echo json_encode(['error' => 1, 'message' => "Forbidden", 'status' => 403]);
                    exit;
                }

                $ticket = $ticket[0];

                // Not allowed to lower max quantity under the current buyers count
                if($max < $ticket['buyer_count']){
                    echo json_encode(['error' => 1, 'message' => "Max attendees cannot be less than the current buyers", 'status' => 200]);
                    exit;
                }
            }

            $endDateTime = new DateTime($end);
            $startDateTime = new DateTime($start);
            $now = new DateTime();

            // End must be >= now and >= start
            if($endDateTime < $now || $endDateTime < $startDateTime){
                echo json_encode(['error' => 1, 'message' => "Please adjust the Sales start and end dates", 'status' => 200]);
                exit;
            }

            // Numeric constraints
            if($max <= 0){
                echo json_encode(['error' => 1, 'message' => "Max attendees cannot be less than or equal to 0", 'status' => 200]);
                exit;
            }

            if($price < 0){
                echo json_encode(['error' => 1, 'message' => "Price cannot be negative", 'status' => 200]);
                exit;
            }

            // Prepare unique(ish) filename for upload (hash-based)
            $hash = hash('sha256', $file_name);
            $shortHash = substr($hash, 0, 12) . "." . $ext;

            // Use hashed name if a file was provided; otherwise keep empty
            $target = $file_name == "" ? $file_name : $shortHash;

            // Move uploaded file to images/ticket_logos/ (only if a file exists in this request)
            if ($file_name && isset($_FILES['file_img']['tmp_name']) && !move_uploaded_file($_FILES['file_img']['tmp_name'], "images/ticket_logos/". $target)) {            
                echo json_encode(['error' => 0, 'message' => "Impossible upload your image. Please retry later.", 'status' => 200]);
                exit;
            }

            if($ticketId){
                // Update existing ticket
                $tModel->updateTicketById($ticketId, htmlentities($title), $ticket['creator_id'], $visibility ? 1 : 0, $max, $start, $end, $target, $price);
            }else {
                // Create new ticket. Creator is current user
                $ticketId = $tModel->insertNewTicket(htmlentities($title), getSession("user_id"), $visibility ? 1 : 0, $max, $start, $end, $target, $price);
            }
            
            // Success
            echo json_encode(['error' => 0, 'message' => "Operation completed successfully", 'status' => 200]);
            exit;

        }catch(Exception $e){

            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 1, 'message' => "Internal Server Error", 'status' => 500]);
            exit;
        }
    }
}