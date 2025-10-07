<?php

namespace Controllers\Auth;

use Error;
use Exception;
use Models\UserModel;

class LoginController
{
    /**
     * Renders login page. Redirects to homepage if user already logged in
     * 
     * @return void
     */
    public function index()
    {
        // If already logged in, send the user to their appropriate homepage
        if(isLoggedIn()){
            header(isOrganizer() ? "Location: /organizer" : "Location: /");
        } else {
            // Clear session
            session_destroy();

            // Render login view
            view('auth/login', []);
        }
    }

    /**
     * Validate login credentials (Ajax endpoint)
     * 
     */
    public function loginAction(){
        // Only accept POST for credential exchange
        if($_SERVER['REQUEST_METHOD'] == 'POST'){

            // Read raw JSON body
            $raw = file_get_contents('php://input');

            // Decode JSON into associative array
            $payload = json_decode($raw, true);
            
            $email = $payload['email'];
            $password = $payload['password'];
            $errorMsg = "";
            $uModel = new UserModel();
            $userId = 0;
            
            // Basic presence + email validation
            if(! empty($email) && ! empty($password) && filter_var($email, FILTER_VALIDATE_EMAIL)){
                try{
                    

                    $result = $uModel->getUserByEmail($email);
                    if($result){
                        
                        if(count($result)>0){

                            // Single user record
                            $result = $result[0];
                            $userId = $result['id'];

                            // Retrieve auth identity (user secrets)
                            $userSecret = $uModel->getUserAuthIdentity($result['id'], $result['email']);
            
                            if(count($userSecret) > 0){
                                
                                if(password_verify($password, $userSecret[0]['secret2'])){

                                    // Get user groups and pick one (prefer 'organizer' if present)
                                    $userGroups = $uModel->getUserGroup($result['id']);
                                    $finalGroup = "";

                                    if(count($userGroups) > 0){
                                        foreach($userGroups as $group){
                                            $finalGroup = $group['user_group'];
                                            if($finalGroup == 'organizer'){
                                                break;
                                            }
                                        }

                                        // Initialize session for authenticated user
                                        setSession('user_id', $result['id']);
                                        setSession('user_name', $result['name']);
                                        setSession('user_group', $finalGroup);
                                        setSession("isLoggedIn", true);

                                        $uModel->insertNewLoginAttempt($result['id'], $email, 1);

                                        // Success
                                        echo json_encode(["error" => 0, "message" => ""]);
                                        exit;
                                    }
                                    
                                    
                                    $errorMsg = "Wrong Group";
                                    
                                } else {
                                    
                                    $errorMsg = "Wrong password";
                                }
                            } else {
                                // No auth identity stored (unexpected state)
                                // Intentionally silent: falls through to generic error at end
                            }
                        } else {
                            
                            $errorMsg = "Invalid username";
                        }
                        
                    } else {
                        
                        $errorMsg = "Invalid username";
                    }
                } catch (Exception $e){
                    
                    //Register login attempt only on valid users
                    if($userId > 0){$uModel->insertNewLoginAttempt($userId, $email, 0);}

                    error_log($e->getMessage());
                    http_response_code(500);
                    echo json_encode(["error" => 1, "message" => "Internal Server Error", "status" => 500]);
                    exit;
                }
                
            } else {
                
                $errorMsg = "Please enter some valid username or password.";
            }
        }

        //Register login attempt only on valid users
        if($userId > 0){$uModel->insertNewLoginAttempt($userId, $email, 0);}
        
        // Unified error response
        echo json_encode(["error" => 1, "message" => $errorMsg, "status" => 200]);
    }


    /**
     * Destroy session and logs user out
     * 
     * @return void
     */
    public function logout(){
        
        session_destroy();

        // Ajax logout or browser navigation
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            echo json_encode(["error"=>0]);
        }else {
            header("Location: /login");
        }
    }
}
