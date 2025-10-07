<?php

// Utilities and view helpers

/**
 * Render a PHP view file and extract $data keys as variables inside it.
 *
 * @param string $name   Relative view path under ../app/Views (without .php)
 * @param array  $data   Variables to extract into the view's scope
 */
function view($name, $data = [])
{
    // Convert keys of $data into local variables for the view
    extract($data);

    // Resolve and include the view if present, or give a red error message and exit
    if (file_exists("../app/Views/$name.php")){
        require_once "../app/Views/$name.php";
    }else{
        echo "<h1 style='color: red;text-align: center'>View [$name] Not found</h1>";
        exit();
    }
}

/**
 * Check if a user session is considered "logged in".
 * Requires both user_id and user_group to be present/truthy.
 *
 * @return bool
 */
function isLoggedIn(){
    return (isset($_SESSION["user_id"]) && $_SESSION['user_id'] && isset($_SESSION['user_group']) && $_SESSION['user_group']);
}

/**
 * Check if the current user is an Organizer.
 * Requires a valid session and user_group === 'organizer'.
 *
 * @return bool
 */
function isOrganizer(){
    return (isset($_SESSION["user_id"]) && $_SESSION['user_group'] === 'organizer');
}

/**
 * Safe getter for a session key.
 *
 * @param string $key
 * @return mixed False if not set; otherwise the stored value
 */
function getSession($key){
    return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
}

/**
 * Set a session key to a given value.
 *
 * @param string $key
 * @param mixed  $value
 * @return void
 */
function setSession($key, $value){
    $_SESSION[$key]= $value;
}

/**
 * Parse a simple KEY=VALUE .env-like file and:
 * - return an associative array of variables
 * - populate $_ENV and the process environment via putenv()
 *
 * @param string $file Absolute or relative path to the env file
 * @return array       Map of name => value
 * @throws Exception   If file is missing
 */
function parseEnvFile(string $file): array {
    if (!file_exists($file)) {
        throw new Exception("Env file not found: $file");
    }

    $vars = [];
    // Load lines, skipping empties
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments
        if (str_starts_with($line, '#')) {
            continue;
        }

        // Split key=value (only the first '=' splits)
        [$name, $value] = explode('=', $line, 2);

        $name  = trim($name);
        $value = trim($value);

        // Remove optional surrounding quotes
        $value = trim($value, "\"'");


        $vars[$name] = $value;

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }

    return $vars;
}

/**
 * Return a formatted date string for inline text.
 *
 * @param string $stringDate  Input timestamp string parseable by DateTime
 * @param bool   $noHours     If true, return a long date-only string
 * @return string
 */
function formatDateInLine($stringDate, $noHours = false){
    $date = new DateTime($stringDate);

    return $noHours ? $date->format('l, F d, Y') : $date->format('M d, Y h:i A');
}

/**
 * Wrap a raw date string inside a div suitable for table cells.
 * Optional extra class can be appended.
 *
 * @param string $stringDate The date/time string to render
 * @param string $class      Additional class name(s)
 * @return string            HTML snippet
 */
function formateDateForTables($stringDate, $class = ""){
    return "<div class='datetime_td $class'>$stringDate</div>";
}

/**
 * Build an img preview element for ticket logos.
 *
 * @param string $image Relative filename under /images/ticket_logos/
 * @return string       HTML <img> tag
 */
function showPreview($image){
    return "<img id='ticket_logo_preview' class='img_preview' src='/images/ticket_logos/". $image . "' alt='Ticket logo' >";
}
