<?php

namespace Config;

use Exception;

class Routing
{
    // Current request path and resolved routing targets 
    private string $path;                 // for instance "/organizer/event/12"
    private array $parameters = [];       // URL params 
    private ?string $controller = null;   // Controller class (string)
    private ?string $method = null;       // Method name to run on controller
    private ?string $authorizedGroup = null; // If set, restricts route to this user_group

    public function __construct()
    {
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);        
    }

    public function getRoute(){
        // Load static route table (expects an array of ['url','controller','method','authorized_group'])
        $routes = require 'Routes.php';

        // Redirect organizers away from "/"
        // This ensure baseUrl to land in homepage for organizers (when logged in)
        if(getSession("user_group") === "organizer" && $this->path === "/" ){
            header("Location: /organizer");
            exit;
        }

        // Attempt to find a matching route definition
        foreach ($routes as $route){
            if ($this->validateRoute($route['url'])){
                // On first match, capture controller/method/access requirements and stop
                $this->controller = 'Controllers\\' .$route['controller'];
                
                $this->method = $route['method'];
                $this->authorizedGroup = $route['authorized_group'];
                break;
            }
        }

        // If we resolved a controller/method and authorizedGroup is satisfied, proceed
        if (
            $this->controller &&
            $this->method &&
            (
                is_null($this->authorizedGroup) ||
                ($this->authorizedGroup && $this->authorizedGroup === getSession('user_group'))
            )
        ){

            // Instantiate controller and run the resolved method with params (if any)
            $instance = new $this->controller;
            $method = $this->method;
            $instance->$method(...$this->parameters);

        }else{
            // No matching route or unauthorized access for this user group
            // For future improvement, land into an error page.
            echo "Route does not exist.";
        }
    }

    private function validateRoute($routeUrl): bool
    {
        // Defined routes pattern split into segments (like ["organizer","event","(:num)"])
        $uri = array_values(array_filter(explode('/', $routeUrl)));

        // Actual request path split into segments (like, ["organizer","event","12"])
        $url = array_values(array_filter(explode('/', $this->path)));

        // Exact parts count must match
        if (count($uri) !== count($url)){
            return false;
        }

        $this->parameters = [];

        // loop through the defined route parts. Flag mismatch
        foreach ($uri as $key => $params){

            if($params === "(:num)" && filter_var($url[$key], FILTER_VALIDATE_INT)){
                // Accept numeric value and capture it as a parameter
                $this->parameters[]= $url[$key];
            } elseif ($params !== $url[$key]){
                // parts not matching. Route does not match
                return false;
            }
        }

        //Match found
        return true;
    } 
}
