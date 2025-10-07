<!-- Cart modal content: nav bar + cart/review phaes -->

<div class='cart_content flex_column' style="padding-top: 10px;">
    
    <!-- Stepper navigation: Cart -> Review -> dynamically added classes to the menu items depending on the phase -->
    <ul class='multi_phases_nav'>
        <li id='cart_review_cart' class='phase_nav_btn navigated active_menu' data-id='cart' ><span class='phase_nav_item'>1</span><span class='phase_nav_text'>Cart</span></li> 
        <li id='cart_review_review' class='phase_nav_btn navigated' data-id='review' ><span class='phase_nav_item'>2</span><span class='phase_nav_text'>Review</span></li>
    </ul>

    <!-- Phase: Cart (active by default) -->
    <div id='phase_section_cart' class='phase_section active_phase'>
        <div class="cart_container">
            <!-- Items in cart (populated via JS) -->
            <div class="cart_items_container">
                
            </div>
            <div class="cart_review_total">
                <span>Total <span class="total_cart_item_number">0</span> item(s): </span>
                <span> $<span class="total_amount">0</span></span>
            </div>
        </div>
        <div class='btn_wrap_margin'>
            <!-- proceed/cacel actions-->
            <?php echo \App\Views\components\Button::generateButton('cart_btn_proceed', "", "Proceed To Review", []); ?>
            <?php echo \App\Views\components\Button::generateButton('cart_btn_cancel', "", "Cancel", []); ?>
        </div>
    </div>

    <!-- Review phase-->
    <div id='phase_section_review' class='phase_section'>
        <div class="cart_review_container">
            <!-- Items to review (read-only, populated via JS) -->
            <div class="cart_items_review_container">
                
            </div>
            <div class="cart_review_total">
                <span>Total <span class="total_cart_item_number">0</span> item(s): </span>
                <span> $<span class="total_amount">0</span></span>
            </div>
        </div>
        <div class='btn_wrap_margin'>
            <!-- checkout/back actions-->
            <?php echo \App\Views\components\Button::generateButton('review_btn_checkout', "review_btns", "Checkout", []); ?>
            <?php echo \App\Views\components\Button::generateButton('review_btn_back', "review_btns", "Back", []); ?>
        </div>
    </div>
</div>