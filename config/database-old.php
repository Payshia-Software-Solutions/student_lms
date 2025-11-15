<?php
// config/database.php

// --- Database Configuration ---
// Set your database connection details here.
$host = 'localhost';
$db   = 'student_lms';
$user = 'root';
$pass = ''; // Default is empty for local XAMPP/WAMP servers
$charset = 'utf8mb4';
// --- End of Configuration ---


// Data Source Name (DSN) for the connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for the connection
$options = [
    // Throw exceptions on SQL errors for robust error handling
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Fetch results as associative arrays for easy access
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Disable emulation of prepared statements for security and performance
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    /**
     * Create a new PDO instance and store it in the global scope.
     * This makes the $pdo variable accessible from any file in the application,
     * which is used by the controllers to interact with the database.
     */
    $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    /**
     * If the database connection fails, a PDOException is thrown.
     * The custom error handler in 'routes/web.php' will catch this,
     * log the error, and send a generic 500 server error response.
     */
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
