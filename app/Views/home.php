<?php

use App\Views\Components\Button;
use App\Views\Components\Popup;
use App\Views\Components\Table;

 require_once "layouts/header.php"; ?>

<!-- Page: Home | Shows public tickets and integrates cart popup -->
<main id="main" class="site-main">
    <!-- Render cart modal into a popup -->
    <?php 
        ob_start();
        include 'cart_modal.php';  // This file outputs some HTML content
        $content = ob_get_clean(); // Get the content as a string

        echo Popup::generatePopup("cart_modal_id", $content, "", true); 
    ?>
    <!-- Cart quick actions (open/empty) and summary -->
    <div class="cart_wrapper">
        <!-- Cart Button -->
        <?php echo Button::generateButton("go_to_cart_btn", "cart_btns", CART_ICON, []) ?>
        <?php echo Button::generateButton("empty_cart_btn", "cart_btns red_tool", DELETE_ICON, []) ?>
        

        <!-- Cart Preview Info -->
        <div class="cart_preview_info">
            <span class="cart_preview_items">0</span> items - 
            $<span class="cart_preview_total">0</span>
        </div>
    </div>
    <!-- Tickets table header -->
    <h3 class='inner_section_title'>Public Tickets</h3>
    <div class='flex_column'>
        <?php 
            // Build table rows from $tickets
            $rows = [];
            foreach($tickets as $t){
                $row = $t;

                $eDate = formateDateForTables($t['ends_at']);

                if($t['sold_out']){
                    
                    // Sold-out overlay
                    $img = "<div class='image-hover'>
                        <img src='images/". ($t['image'] ? ("ticket_logos/" . $t['image']) : "placeholder_logo.png") . "' alt='Event logo' class='bottom-img'>
                        <img src='images/sold_out.png' alt='Sold Out' class='top-img'>
                    </div>";                    
                }else{
                    $img = "<img class='img_preview event_logo' src='images/". ($t['image'] ? ("ticket_logos/" . $t['image']) : "placeholder_logo.png") . "' alt='Event logo' >"; 
                }
                
                $title = $t['title_tooltip'] != "" ? 
                    Button::generateToolButton("sold_out_".$t['id'], "red_tool no_hover", EXCLAMATION_ICON, [], ["disabled" => false, "tooltip" => $t['title_tooltip']]) ."  ". $t['title'] :
                    $t['title'];

                // row quantity controls and add to cart button
                $toolBox = 
                '<div class="table_cart flex_row_center">'.
                    '<div class="quantity_control">'.
                        Button::generateToolButton("", "qty_btn plus", PLUS_ICON, [], ["disabled" => $t['sold_out'], "tooltip" => ""]) .
                        "<input type='number' name='quantity' id='quantity_value_". $t['id'] . "' value='0' min='0' max='". $t['tickets_available'] ."' class='qty_input'>" . 
                        Button::generateToolButton("", "qty_btn minus", MINUS_ICON, [], ["disabled" => $t['sold_out'], "tooltip" => ""]) .
                        Button::generateButton("", "add_to_cart_btns", "Add to cart", [$t['id'], $t['price'], $t['title']], [$t['sold_out'], ""]) . 
                    '</div>' .
                    
                '</div>';
                

                $rows[]= [
                    $img,
                    $title,
                    $t['name'],
                    "$".$t['price'],
                    $eDate,
                    $t['tickets_available'],
                    $toolBox
                ];
            }

            // Instantiate Table component and render
            $table = new Table("/", $headers, $rows, []);

            echo $table->tableHtml("home_events_table");
        ?>


    </div>
</main>

<!-- Cart modal js -->
<script src="/js/cart.js"></script>

<script>
    // Bind cart UI interactions and wire up buttons
    $(document).ready(function(){

        // Initialize popup/listeners for cart modal
        addCartListeners("cart_modal_id")

        // Open cart modal
        $("#go_to_cart_btn").on("click",function() {
            triggerPopup("cart_modal_id");
        })
        
        // Increment quantity button
        $(".qty_btn.plus").on("click", function(){
            let $input = $(this).siblings(".qty_input");
            let max = parseInt($input.attr("max"));
            let current = parseInt($input.val());
            if(! max || current < max){
                $input.val(parseInt($input.val()) + 1);
            }
            toggleAddToCartBtns();
        });

        // Decrement quantity button
        $(".qty_btn.minus").on("click", function(){
            let $input = $(this).siblings(".qty_input");
            let min = parseInt($input.attr("min")) || 0;
            let current = parseInt($input.val());
            if(current > min) {
                $input.val(current - 1);
            }
            toggleAddToCartBtns();
        });

        // Bound manual quantity entry to min/max
        $(".qty_input").on("change", function(){            
            let current = parseInt($(this).val());

            let max = parseInt($(this).attr("max"));
            
            if(current > max){
                $(this).val(max);
            }
            
            let min = parseInt($(this).attr("min")) || 0;

            if(current < min) {
                $(this).val(min);
            }

            toggleAddToCartBtns();
        })


        // Add selected quantity to cart
        $(".add_to_cart_btns").on("click",function(){

            let $parameters = $(this).data("jsparams");
            
            let tId = $parameters[0];

            let current = parseInt($("#quantity_value_"+tId).val());
            let price = $parameters[1];
            let title = $parameters[2];

            cart.addItem(tId, current, price, title);
            $("#quantity_value_"+tId).val(0)
            toggleAddToCartBtns();

            let item = cart.getItem(tId);
            
            updateItemInCart(tId, item.quantity, item.pricePerUnit, item.name)
            setTotalCart();
            toggleBtnsProceed();
        })

        // Empty cart and reset UI
        $("#empty_cart_btn").on("click",function(){
            cart.clearCart();
            $(".qty_input").val(0);
            toggleAddToCartBtns();
            removeAllItemsInCart();
            setTotalCart();
            popupMessage("Success! Your cart is empty now.");
        })

        // Enable "Add to cart" only when quantity > 0
        function toggleAddToCartBtns(){
            $(".add_to_cart_btns").each(function(){
                $(this).prop("disabled", $(this).siblings(".qty_input").val() == 0)
            })
        }

        // Restore cart from storage and update UI
        populateCart();
        toggleAddToCartBtns();
    });
</script>
<?php require_once "layouts/footer.php"; ?>