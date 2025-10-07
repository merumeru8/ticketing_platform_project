<?php

use App\Views\Components\Button;

 require_once __DIR__ . "/../layouts/header.php"; ?>
     
<!-- Register view -->
<mainid="main" class="site-main">
    <article class="page type-page">
        <!-- Main content, login form -->
        <div class="entry-content clear">
            <div style="height: 48px" aria-hidden="true"></div>
            <h3 class="has-text-align-center">Register new account</h3>

            <div class="flex_column_center" style="opacity: 1">
                <div class="login-form">
                    <!-- Authentication form -->
                    <form method="post"  action="/register_action" autocomplete="off" data-hs-cf-bound="true">
                        
                        <div style="padding: 0px 0px 0px 0px;margin: 0px 0px 30px 0px; border-width: 0px 0px 0px 0px; border-style: solid; border-radius: 0px;">
                            <div class="col-1">
                                <!-- Username -->
                                <div id="username_field">
                                    <div class="">
                                        <label for="name_input">Name</label>
                                        <div class=""></div>
                                    </div>
                                    <div class="">
                                        <!-- Name field -->
                                        <input type="text" name="name" id="name_input" value="" placeholder="" autocomplete="off" class="login-field valid" data-validate="unique_username_or_email" data-key="name" required/>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div id="email_field">
                                    <div class="">
                                        <label for="email_input">E-mail</label>
                                        <div class=""></div>
                                    </div>
                                    <div class="">
                                        <!-- Email field -->
                                        <input type="text" name="email" id="email_input" value="" placeholder="" inputmode="email" autocomplete="off" class="login-field valid" required/>
                                    </div>
                                </div>
                                
                                <!-- Password -->
                                <div id="password_field">
                                    <div class="">
                                        <label for="password_input">Password</label>
                                        <div class=""></div>
                                    </div>
                                    <div class='flex_column'>
                                    <div class="" >
                                        <!-- Password field -->
                                        <input type="password" name="password"id="password_input" value="" inputmode="text" autocomplete="current-password" class="login-field valid password_fields" required/>
                                        <div class="field-error" style="display: none;">
                                            <span class="field-arrow">
                                                <i class="faicon-caret-up"></i>
                                            </span>
                                            <span id='error_msg_text'></span>
                                        </div>
                                    </div>

                                    <div class='flex_row_center show_password'><input id="show_password_1" class='toggle-password' type='checkbox'><label for="show_password_1">Show Password</label></div>
                                    </div>
                                </div>

                                <!-- confirm pass -->
                                <div id="confirm_password_field" >
                                    <div class="">
                                        <label for="confirm_password_input">Confirm password</label>
                                        <div class=""></div>
                                    </div>
                                    <div class='flex_column'>
                                    <div class="" >
                                        <!-- Confirm password field -->
                                        <input type="password" name="confirm_password" id="confirm_password_input" value="" inputmode="text" autocomplete="current-password" class="login-field valid password_fields" required/>
                                    </div>

                                    <div class='flex_row_center show_password'><input id="show_password_2" class='toggle-password' type='checkbox'><label for="show_password_2">Show Password</label></div>
                                    </div>
                                </div>

                                <!-- User Group -->
                                <div id="group_field">
                                    <div class="">
                                        <label for="email_input">Role</label>
                                        <div class=""></div>
                                    </div>
                                    <div class="">
                                        <!-- User role select -->
                                        <select name="user_group"id="user_group_select">
                                            <option value='organizer'>Organizer</option>
                                            <option value='customer'>Customer</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="center-form">
                                     <!-- Submit button -->
                                    <?= Button::generateButton("submit-btn", "submit_btns", "Register", []) ?>
                                </div>

                            </div>
                        </div>

                        <div class="center-form">
                            <!-- Link back to login -->
                            <a href="/login" class=""> Login instead</a>
                        </div>
                    </form>
                </div>
            </div>                
        </div>
    </article>
</main>


<script>
    $(".show_password").on("click", function() {
        let $input = $(this).prev("div").find("input");
        $input.attr("type", $input.attr("type") === "password" ? "text" : "password");
    });

    // submit action
    $(document).ready(function(){
        $("#submit-btn").on("click", function (){
            $(this).prop("disabled", true);

            let info = {};

            let parameters = $(this).data("jsparams");

            info['name'] = $('#name_input').val();
            info['email'] = $('#email_input').val();
            info['password'] = $('#password_input').val();
            info['confirm_password'] = $('#confirm_password_input').val();
            info['user_group'] = $('#user_group_select').val();

            // Async: send form payload to server (fetchAPIJSON)
            let promise = fetchAPIJSON("/register/action", info, "POST");

            promise.then((data) => {
                if(data){
                    
                    if(! data.error){
            
                        location.href = "/login";
                        
                    } else {
                        $("#error_msg_text").text(data.message);
                        $(".field-error").show();
                        $("#password_input").addClass("error");
                        $(this).prop("disabled", false);
                    }
                }
            })
        })
    })
   
</script>

<?php require_once __DIR__ . "/../layouts/footer.php"; ?>