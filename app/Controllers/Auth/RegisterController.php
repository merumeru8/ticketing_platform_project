<?php

namespace Controllers\Auth;

use Error;
use Exception;
use Models\UserModel;

class RegisterController
{
    /**
     * Renders registration page. Redirects to homepage if user already logged in
     * 
     * @return void
     */
    public function index()
    {
        // If already logged in, route to appropriate home page
        if(isLoggedIn()){
            
            header(isOrganizer() ? "Location: /organizer" : "Location: /");
        } else {
            // Fresh registration page load clear any existing session
            session_destroy();

            // Render registration view
            view('auth/register', []);
        }
    }

    /**
     * Validate user credentials and creates new users (Ajax JSON endpoint)
     * 
     * @return void
     */
    public function registerAction(){
        // Only accept POST for registration
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            // Read raw JSON request body
            $raw = file_get_contents('php://input');

            // Decode JSON into associative array
            $payload = json_decode($raw, true);

            $name = $payload['name'] ?? ""; // name is not required
            $email = $payload['email'];
            $password = $payload['password'];
            $confirm = $payload['confirm_password'];
            $group = $payload['user_group'];
            
            //Defaults for rollback
            $errorMsg = "";
            $userId = null;       
            $identityId = null;  
            
            $uModel = new UserModel();

            // presence checks, email format, and selected group
            if(! empty($email) && ! empty($password) && ! empty($confirm) && filter_var($email, FILTER_VALIDATE_EMAIL) && ! empty($group)){
                try{

                    //Checking for existing users
                    $existing = $uModel->getUserByEmail($email);
                    if ($existing && count($existing) > 0) {
                        echo json_encode(["error" => 1, "message" => "User already exists, please use a different email or login", "status" => 200]);
                        exit;
                    }

                    if($password == $confirm){
                        
                        // Only allow the two known roles
                        if($group == 'organizer' || $group == 'customer'){

                            $userId = $uModel->insertNewUser($name, $email);
            
                            if($userId){
                                // Create authentication identity (stores hashed password)
                                $identityId = $uModel->insertNewIdentity($userId, $email, $password);

                                if($identityId){
                                    // Assign selected role to the new user
                                    $uModel->insertNewUserGroup($userId, $group);
                                    
                                    // Success 
                                    echo json_encode(["error" => 0, "message" => ""]);
                                    exit;
                                }else {
                                    // Identity creation failed
                                    $errorMsg = "Failed";
                                }
                                    
                            } else {
                                // User creation failed
                                $errorMsg = "Failed";
                            }
                        } else {
                            
                            $errorMsg = "Invalid group";
                        }
                        
                    } else {
                        
                        $errorMsg = "Passwords not matching";
                    }
                } catch (Exception $e){
                    // Rollback on any server-side error during registration flow
                    // This is needed in order to avoid inconsistency between users, auth_identities_users, and auth_groups_users

                    error_log($e->getMessage());

                    if($userId){
                        $uModel->deleteUser($userId);
                    }

                    if($identityId){
                        $uModel->deleteUserIdentity($userId);
                    }

                    http_response_code(500);
                    echo json_encode(["error" => 1, "message" => "Internal Server Error", "status" => 500]);
                    exit;
                }
                
            } else {
                
                $errorMsg = "Please enter some valid email and fill every field.";
            }
        }

        // Unified error response
        echo json_encode(["error" => 1, "message" => $errorMsg, "status" => 200]);
    }
}
