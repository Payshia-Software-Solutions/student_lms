<?php
// /config/alter_student_table.php

// !!! SECURITY WARNING !!!
// This script is for a one-time database migration.
// It is designed to be run from a web browser and is protected by a secret key.
// AFTER YOU RUN THIS SUCCESSFULLY, YOU MUST DELETE THIS FILE.

$expected_secret = 'e4a2f8c7-4b6e-4b0d-82a8-3b7f2a1e9c5d';
$provided_secret = isset($_GET['secret']) ? $_GET['secret'] : '';

if ($provided_secret !== $expected_secret) {
    header("HTTP/1.1 403 Forbidden");
    echo "<h1>403 Forbidden</h1>";
    echo "<p>Invalid or missing secret key.</p>";
    exit;
}

// Function to print messages to the browser
function browser_echo($message) {
    echo htmlspecialchars($message) . "<br>" . PHP_EOL;
    flush();
}

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Alter Student Table</title></head><body><pre>';

browser_echo("Starting student table alteration...");

require_once __DIR__ . '/Database.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    browser_echo("ERROR: Database connection failed. Please check your credentials in config/Database.php");
    exit(1);
}

browser_echo("Database connection successful.");

try {
    browser_echo("Attempting to rename 'firstname' to 'first_name'...");
    $sql1 = "ALTER TABLE students CHANGE firstname first_name VARCHAR(255) NOT NULL";
    $db->exec($sql1);
    browser_echo("Column 'firstname' renamed to 'first_name' successfully.");

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown column \'firstname\'') !== false || strpos($e->getMessage(), 'ER_BAD_FIELD_ERROR') !== false) {
        browser_echo("Column 'firstname' does not exist, it may have been renamed already.");
    } else {
        browser_echo("ERROR renaming 'firstname': " . $e->getMessage());
    }
}

try {
    browser_echo("Attempting to rename 'lastname' to 'last_name'...");
    $sql2 = "ALTER TABLE students CHANGE lastname last_name VARCHAR(255) NOT NULL";
    $db->exec($sql2);
    browser_echo("Column 'lastname' renamed to 'last_name' successfully.");

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown column \'lastname\'') !== false || strpos($e->getMessage(), 'ER_BAD_FIELD_ERROR') !== false) {
        browser_echo("Column 'lastname' does not exist, it may have been renamed already.");
    } else {
        browser_echo("ERROR renaming 'lastname': " . $e->getMessage());
    }
}

browser_echo("Student table alteration script finished.");

echo '</pre></body></html>';
