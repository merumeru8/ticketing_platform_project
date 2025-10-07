<?php
// scripts/create_db.php
// Create the database (if it doesn't exist) and run db/schema.sql.
//
// Usage: php scripts/create_db.php
//
// Reads DB settings from .env (if present) via your parseEnvFile helper.
// Targets MySQL/MariaDB via mysqli.
//

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load env + helpers
$root = dirname(__DIR__);
$helpers = $root . '/app/Helpers/functions.php';
if (file_exists($helpers)) {
    require_once $helpers; // for parseEnvFile(...)
}

// Load .env if present (harmless if missing)
$envPath = $root . '/.env';
if (function_exists('parseEnvFile') && file_exists($envPath)) {
    parseEnvFile($envPath);
}


$host = getenv('host') ?: '127.0.0.1';
$port = (int)(getenv('port') ?: 3306);
$user = getenv('username') ?: getenv('username') ?: 'root';
$pass = getenv('password') ?: getenv('password') ?: '';
$db   = getenv('dbname') ?: getenv('dbname') ?: 'tickets_app';
$charset   = getenv('charset') ?: 'utf8mb4';
$collation = getenv('collation') ?: 'utf8mb4_unicode_ci';

$schemaPath = $root . '../db/schema.sql';

$schema = file_get_contents($schemaPath);

// Connect to MySQL without DB first (to create it)
try{
    $mysqli = new mysqli($host, $user, $pass, '', $port);
}catch(Exception $e){
    error_log($e->getMessage());
}

if ($mysqli->connect_errno) {
    echo "[ERR] Connection failed ({$mysqli->connect_errno}): {$mysqli->connect_error}\n";
    exit(1);
}
echo "[OK] Connected to MySQL {$host}:{$port} as {$user}\n";

// Crete DB if not exists
$sqlCreate = sprintf(
    "CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s",
    $mysqli->real_escape_string($db),
    $mysqli->real_escape_string($charset),
    $mysqli->real_escape_string($collation)
);

if (!$mysqli->query($sqlCreate)) {
    echo "[ERR] Database creation failed: {$mysqli->error}\n";
    exit(1);
}

echo "[OK] Ensured database exists: {$db}\n";

// Select DB
if (!$mysqli->select_db($db)) {
    echo "[ERR] Could not select database '{$db}': {$mysqli->error}\n";
    exit(1);
}

echo "[OK] Using database: {$db}\n";


//  Execute schema 
echo "[..] Applying schema from db/schema.sql ...\n";

if (!$mysqli->multi_query($schema)) {
    echo "[ERR] Schema execution failed at first statement: {$mysqli->error}\n";
    exit(1);
}

// Loop through all result sets to complete multi_query
do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

// Check for any error
if ($mysqli->errno) {
    echo "[ERR] Schema execution error: ({$mysqli->errno}) {$mysqli->error}\n";
    exit(1);
}

echo "[OK] Schema applied successfully.\n";
echo "[DONE] Database is ready.\n";

$mysqli->close();
