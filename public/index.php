<?php

date_default_timezone_set('America/New_York');

session_start();

// Helpers + Constants
require_once('../app/Helpers/functions.php');
require_once ('../app/Config/constants.php');

//Simple autoloader to automatically load classes without using Composer
spl_autoload_register(function ($name) {

    $prefix = 'App\\';
    if (strpos($name, $prefix) === 0) {
        $name = substr($name, strlen($prefix));
    }

    $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

if (php_sapi_name() !== 'cli'){
    //custom parser for environmental variables
    parseEnvFile(".." . DIRECTORY_SEPARATOR . ".env");
    
    //Instanciate the controller and run the method requested by the route
    $router = new Config\Routing();
    $router->getRoute();
}