<?php

 require_once __DIR__ . "/../layouts/header.php"; ?>
     
<!-- Page: Login view | Comments added for structure and JS hooks -->
<!-- Main content wrapper -->
<main id="main" class="site-main">
    <article class="page type-page">
        <!-- Main content, login form -->
        <div class="entry-content clear">
            <div style="height: 48px" aria-hidden="true"></div>
            <h3 class="has-text-align-center">Login</h3>

            <div id='last_action_status_bar' class='last-action disclaimer disclaimer_success no_auth_screen'>Please login before completing the checkout</div>

            <div class="flex_column_center" style="opacity: 1">
                <div class="login-form flex-column-center">
                    <!-- Authentication form -->
                    <form method="post" autocomplete="off" data-hs-cf-bound="true">
                        
                        <div class="form-row" style="padding: 0px 0px 0px 0px;margin: 0px 0px 30px 0px; border-width: 0px 0px 0px 0px; border-style: solid; border-radius: 0px;">
                            <div class="col-1">
                                <!-- Email -->
                                <div id="username_field" class="form-field field-text" data-key="username">
                                    <div class="field-label">
                                        <label for="email_input">E-mail</label>
                                        <div class=""></div>
                                    </div>
                                    <div class="field-area">
                                        <!-- Email field -->
                                        <input type="text" name="email" id="email_input" value="" placeholder="" inputmode="email" autocomplete="off" class="login-field valid" data-validate="unique_username_or_email" data-key="email" required/>
                                    </div>
                                </div>
                                
                                <!-- Password -->
                                <div id="password_field" class="form-field field-password" data-key="user_password">
                                    <div class="field-label">
                                    <label for="password_input">Password</label>
                                    <div class=""></div>
                                    </div>
                                    <div class='flex_column'>
                                    <div class="field-area" >
                                        <!-- Password field -->
                                        <input type="password" name="password" id="password_input" value="" inputmode="text" autocomplete="current-password" class="login-field valid" data-validate="" data-key="user_password" required/>
                                        <div class="field-error"  style="display: none;" >
                                            <span class="field-error-arrow">
                                                <i class="fas fa-caret-up"></i>
                                            </span>
                                            <span id='error_msg_text'></span>
                                        </div>
                                    </div>

                                    <div class='flex_row_center'><input id="show_password" class='toggle-password' type='checkbox'><label for="show_password">Show Password</label></div>
                                    </div>
                                </div>

                                <div class="center-form">
                                    <!-- Submit button -->
                                    <?= \App\Views\components\Button::generateButton("submit-btn", "submit_btns", "Login", []) ?>
                                </div>
                            </div>
                        </div>

                        <div class="center-form">
                            <!-- Link to registration -->
                            <a href="/register" class="link-alt"> Create new account</a>
                        </div>
                    </form>
                </div>
            </div>                
        </div>
    </article>
</main>


<script>
    // toggles password input type
    $(".show_password").on("click", function() {
        let $input = $(this).prev("div").find("input");
        $input.attr("type", $input.attr("type") === "password" ? "text" : "password");
    });

    // submit click
    $(document).ready(function(){
        localStorage.getItem("from_checkout") ? $("#last_action_status_bar").show() : $("#last_action_status_bar").hide();

        $("#submit-btn").on("click", function (){
            $(this).prop("disabled", true);

            let info = {};

            let parameters = $(this).data("jsparams");

            info['email'] = $('#email_input').val();
            info['password'] = $('#password_input').val();
            
            
            let promise = fetchAPIJSON("/login/action", info, "POST");

            promise.then((data) => {
                if(data){
                    
                    if(! data.error){

                        if(localStorage.getItem("from_checkout")){

                            let info2 = {};
                            info2['cart'] = JSON.parse(localStorage.getItem('cart'));
                            
                            let promise2 = fetchAPIJSON("/customer/checkout", info2, "POST");
                            promise2.then((data2) => {

                                if(data2){

                                    localStorage.setItem("message", data2.message);

                                    if(! data2.error){
                                        localStorage.removeItem("cart");
                                        localStorage.removeItem("from_checkout");
                                    }

                                    location.href = "/";
                                }else {
                                    location.href = "/logout";
                                }

                            })
                        } else {
                            location.href = "/";
                        }

                        
                    } else {
                        $("#error_msg_text").text(data.message);
                        $(".field-error").show();
                        $("#password_input").addClass("error-block");
                        $(this).prop("disabled", false);
                    }
                }
            })
        })
    })
</script>

<?php require_once __DIR__ . "/../layouts/footer.php"; ?>