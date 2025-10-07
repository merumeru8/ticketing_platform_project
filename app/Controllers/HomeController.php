<?php

namespace Controllers;

use Exception;
use Models\TicketModel;

class HomeController extends \Config\BaseController
{

    /**
     * Renders home page for not logged in users or customers
     * 
     */
    public function index()
    {
       
        $tModel = new TicketModel();

        // Fetch all public tickets (for future improvement we can add search fitler)
        $myTickets = $tModel->getAllPublicTickets("");

        $mList = [];
        $data = [];

        foreach($myTickets as $t){
            $modified = $t;
            
            // Sold out flag 
            $modified['sold_out']= $t['buyer_count'] >= $t['max_quantity'];
            
            // user friendly end date (like "Monday, January 01, 2025")
            $modified['ends_at'] = formatDateInLine($t['ends_at'], true);

            // Remaining inventory
            $modified['tickets_available'] = $t['max_quantity'] - $t['buyer_count'];


            // Optional tooltip for less than 5 tickets remaining
            $modified['title_tooltip'] = "";

            if($modified['tickets_available'] < 5 && $modified['tickets_available'] > 0){
                $modified['title_tooltip'] = "Only ". $modified['tickets_available'] . " remaining!";
            }
            
            $mList[]= $modified;
        }

        // Table rows for the view component
        $data['tickets'] = $mList;
        
        // Table headers for the view component
        $data['headers'] = ["", "Title", "Creator", "Price/Ticket", "Sales End", "Tickets Available", ""];
        
        // Render homepage view
        view('home', $data);
    }
}
