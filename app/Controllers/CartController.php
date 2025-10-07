<?php

namespace Controllers;

use Exception;
use Models\BuyerModel;
use Models\TicketModel;

class CartController extends \Config\BaseController
{
    /**
     * Checkout process (Ajax JSON endpoint)
     * 
     */
    public function checkout(){
        // If the user is not authenticated, the client will redirect
        if(! isLoggedIn()){

            echo json_encode(["redirect" => 1]);
        } elseif(! isOrganizer()) {// only customer users can complete a checkout

            // Read raw JSON body from the request
            $raw = file_get_contents('php://input');

            // Decode JSON into associative array
            $payload = json_decode($raw, true);

            // Cart payload structure: { ticketId: { quantity: N, ... }, ... }
            $cart = $payload['cart'];
            $tickets = [];     // ticketId => sanitied quantity
            $totalPrice = 0;   // cumulative total

            $tModel = new TicketModel();

            try{
                
                foreach($cart as $key => $values){
                    
                    // Fetch ticket info (only if public)
                    $tInfo = $tModel->getTicketById($key, 1);

                    if($tInfo && count($tInfo) > 0){
                        // Maximum purchasable = remaining capacity
                        $quantity = min([$values['quantity'], $tInfo[0]['max_quantity'] - $tInfo[0]['buyer_count']]);

                        // Only add valid positive quantities
                        if($quantity > 0){
                            $tickets[$key]= $quantity;
                            $totalPrice+= $quantity*$tInfo[0]["price"];
                        }
                    }
                }
            }catch(Exception $e){
                
                error_log($e->getMessage());
                echo json_encode(["error" => 1, "message" => "Internal server error", "status" => 500]);
                http_response_code(500);
                exit;
            }
            
            // If nothing valid remains after validation, error and exit
            if(count($tickets) == 0){
                echo json_encode(["error" => 1, "message" => "No valid tickets to checkout.", "status" => 200]);
                exit;
            }

            try{
                // One buyer across multiple tickets
                $bModel = new BuyerModel();
                $bModel->insertNewBuyerForMultiTicket(getSession("user_id"), $tickets);                
                
                echo json_encode(["error" => 0, "message" => "Success! Total paid $$totalPrice.", "status" => 200]);
                
                exit;
            } catch(Exception $e){

                error_log($e->getMessage());
                echo json_encode(["error" => 1, "message" => "Internal server error", "status" => 500]);
                http_response_code(500);   
            }
        } else {
            
            echo json_encode(["error" => 1, "message" => "Forbidden", "status" => 403]);
            http_response_code(403);   
        }
    }
}