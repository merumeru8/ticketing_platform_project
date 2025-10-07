<?php

use App\Views\Components\Button;
use App\Views\Components\Table;

require_once "layouts/header.php"; ?>

<!-- Page: Organizer Home | Manage your events list -->
<main id="main" class="site-main">
    <!-- Actions: create new event -->
    <div class='flex_row_center' style="direction: rtl;"><a href='/organizer/event' ><?php echo Button::generateButton("create_new_event_btn", "", "Create new Ticket", []) ?></a></div>
    <!-- Listing header -->
    <h3 class='inner_section_title'>My Tickets</h3>
    <div class='flex_column'>
        <?php 
            // Build table body with event rows
            $rows = [];
            foreach($tickets as $t){
                $row = $t;

                //$visibility = $t['visibility'] ? 
                //    Button::generateToolButton("visibility_".$t['id'], "green_tool visible_event_icon no_hover", STORE_ICON, [], ["disabled" => false, "tooltip" => "Public"]) :
                //    Button::generateToolButton("visibility_".$t['id'], "yellow_tool no_hover", LOCK_ICON, [], ["disabled" => false, "tooltip" => "Private"]);

                $visibility = $t['visibility'] ? 
                    "<div class='blue_status'>Public</div>" : 
                    "<div class='gray_status'>Private</div>";

                // Choose title link or plain text depending on deleted state
                if($deleted){
                    $title = $t['title'];
                }else {
                    $title = "<a href='/organizer/event/".$t['id']."'>". $t['title'] . "</a>";
                    $title = $t['sold_out'] ? 
                        Button::generateToolButton("sold_out_".$t['id'], "red_tool no_hover", EXCLAMATION_ICON, [], ["disabled" => false, "tooltip" => "Sold out!"]) ."  ". $title :
                        $title;
                }

                $cDate = formateDateForTables($t['created_at']);
                $sDate = formateDateForTables($t['starts_at']);
                $eDate = formateDateForTables($t['ends_at']);

                // Ticket logo fallback to placeholder
                $img = "<img class='img_preview event_logo' src='images/". ($t['image'] ? ("ticket_logos/" . $t['image']) : "placeholder_logo.png") . "' alt='Ticket logo' >";

                // Tool buttons: edit/delete or restore
                $t['deleted'] ? 
                    $toolBox = Button::toolsBox("", ["restore_event_btns"], [RESTORE_ICON], [[$t['id']]], ["Restore"], [""], [false]) :
                    $toolBox = Button::toolsBox("", ["blue_tool edit_event_btns", "red_tool delete_event_btns"], [EDIT_ICON, DELETE_ICON], [[$t['id']],[$t['id']]], ["Edit", "Delete"], [$t['past'], $t['past']]);
                

                $rows[] = [
                    $img,
                    $title,
                    $visibility,
                    "$".$t['price'],
                    $cDate,
                    $t['max_quantity'],
                    $t['buyer_count'],
                    $sDate,
                    $eDate,
                    $toolBox
                ];
            }

            // Instantiate Table with search + archived toggle
            $table = new Table("/organizer", $headers, $rows, ['Active Tickets' => !$deleted, 'Deleted Tickets' => $deleted], $search);

            echo $table->tableWithSearchDisplay("organizer_events_table");
        ?>


    </div>
</main>

<script>
    $(document).ready(function(){
        
        // Delete event (soft-delete)
        $(".delete_event_btns").on("click", function(){
            let parameters = $(this).data("jsparams");

            let info = {};

            info['ticketId'] = parameters[0];

            let promise = fetchAPIJSON("/organizer/delete_event", info, "POST");

            promise.then((data) => {
                if(data){
                    localStorage.setItem("message", data.message);
                    location.href = "/organizer";
                }
            })
        })

        // Navigate to edit page
        $(".edit_event_btns").on("click", function(){
            let parameters = $(this).data("jsparams");
            
            location.href = "/organizer/event/"+parameters[0];
        })

        // Restore previously deleted event
        $(".restore_event_btns").on("click", function(){
            let parameters = $(this).data("jsparams");

            let info = {};

            info['ticketId'] = parameters[0];

            let promise = fetchAPIJSON("/organizer/restore_event", info, "POST");

            promise.then((data) => {
                if(data){
                    localStorage.setItem("message", data.message);
                    location.href = "/organizer";
                }
            })
        })
    })
</script>
<?php require_once "layouts/footer.php"; ?>