<?php

namespace Controllers;

use DateTime;
use Exception;
use Models\BuyerModel;
use Models\TicketModel;

// Controller for organizer's event list and actions (soft delete/restore)
class OrganizerHomeController extends \Config\BaseController
{
    
    /**
     * Renders organizer home page
     * 
     */
    public function index()
    {

        $tModel = new TicketModel();

        // Optional filters pulled from query string
        $deleted = $_GET['deleted'] ?? 0;           // 0 => active, 1 => deleted view
        $search = $_GET['search'] ?? "";            // raw search query (may include % from UI)

        // Normalize search (strip % wildcrds coming from UI, DB layer can add its own)
        $search = str_replace("%", "", $search);

        // Fetch tickets owned by the current organizer, filtered by deleted flag + search
        $myTickets = $tModel->getTicketsByOrganizer(getSession("user_id"), $deleted, $search);

        // Build view model
        $mList = [];
        $data = [];

        $now = new DateTime();
        foreach($myTickets as $t){
            $modified = $t;

            // Parse time boundaries
            $sDate = new DateTime($t['starts_at']);
            $eDate = new DateTime($t['ends_at']);

            // set flags and dates
            $modified['sold_out']= $t['buyer_count'] >= $t['max_quantity']; // capacity reached
            $modified['past'] = $now >= $eDate;                              // sales ended
            $modified['not_started'] = $now < $sDate;                        // sales not started yet
            $modified['starts_at'] = formatDateInLine($t['starts_at'], true);
            $modified['ends_at'] = formatDateInLine($t['ends_at'], true);
            $modified['created_at'] = formatDateInLine($t['created_at'], true);

            $mList[]= $modified;
        }

        // Table data fr the view (headers + UI flags + rows)
        $data['tickets'] = $mList;
        $data['headers'] = ["", "Title", "Visibility", "Price/Ticket", "Created on", "Max Attendees", "Tickets Sold", "Sales Start", "Sales End", ""];
        $data['deleted'] = $deleted;
        $data['search'] = htmlentities($search); // escape for safe echo in input fields

        // Render the organizer home page
        view('organizer_home', $data);
    }

    /**
     * Soft delete an event (Ajax JSON endpoint)
     * 
     * @return void
     */
    public function deleteEvent(){
        // Read raw JSON payload
        $raw = file_get_contents('php://input');

        // Decode JSON into associative array
        $payload = json_decode($raw, true);

        $ticketId = $payload['ticketId'];

        $tModel = new TicketModel();

        try{
            // ensure the ticket belongs to this organizer and is not deleted
            $ticket = $tModel->getTicketIfOwner(getSession("user_id"), $ticketId, 0);

            if(! $ticket || count($ticket) == 0){
                // Forbidden when ticket not found or not owned by user
                http_response_code(403);
                echo json_encode(['error' => 1, 'message' => "Forbidden", 'status' => 403]);
                exit;
            }

            // Perform soft delete
            $result = $tModel->softDeleteTicket($ticketId, getSession("user_id"));

            if($result){
                // archive buyers for this ticket to keep state consistent
                $bModel = new BuyerModel();

                $bModel->archiveTicketBuyers($ticketId, "Ticket deleted, all buyers archived");
            }

            // Success response
            echo json_encode(['error' => 0, 'message' => "Ticket deleted successfully", 'status' => 200]);
            exit;

        }catch(Exception $e){
            
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 1, 'message' => "Internal Server Error", 'status' => 500]);
            exit;
        }
    }

    
    /**
     * Restore a previously soft deleted event (Ajax JSON endpoint)
     * 
     */
    public function restoreEvent(){
        // Read raw JSON payload
        $raw = file_get_contents('php://input');

        // Decode JSON into associative array
        $payload = json_decode($raw, true);
       
        $ticketId = $payload['ticketId'];

        $tModel = new TicketModel();

        try{
            // Ensure the ticket belongs to this organizer and IS deleted
            $ticket = $tModel->getTicketIfOwner(getSession("user_id"), $ticketId, 1);

            if(! $ticket || count($ticket) == 0){
                // Forbidden when ticket not found or not owned by user
                http_response_code(403);
                echo json_encode(['error' => 1, 'message' => "Forbidden", 'status' => 403]);
                exit;
            }

            // Perform restore
            $result = $tModel->restoreTicket($ticketId, getSession("user_id"));

            if($result){
                // Unarchive buyers associated with this ticket
                $bModel = new BuyerModel();

                $bModel->unarchiveTicketBuyers($ticketId);
            }

            // Success response
            echo json_encode(['error' => 0, 'message' => "Ticket restored successfully", 'status' => 200]);
            exit;

        }catch(Exception $e){

            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 1, 'message' => "Internal Server Error", 'status' => 500]);
            exit;
        }
    }
}