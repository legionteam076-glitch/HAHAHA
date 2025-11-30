<?php
/*
 * Database Configuration for Asterisk Roleplay Whitelist Application
 *
 * NOTE: Replace the placeholder values below with your actual database credentials.
 * The database name is derived from your whitelistapp.sql: `s4_core`.
 */
define('DB_SERVER', 'serverdb.oceannodes.cloud'); // Database host (usually 'localhost')
define('DB_USERNAME', 'u4_jSc996MNOy');    // Database username
define('DB_PASSWORD', 'W2UxS21WX2aAI!3M2u+.!ui1'); // Database password
define('DB_NAME', 's4_core');     // Database name (s4_core based on SQL file)

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect to the database. " . $conn->connect_error);
}

// Set character set to prevent encoding issues
$conn->set_charset("utf8mb4");
?>