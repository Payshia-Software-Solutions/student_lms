<?php
// /config/alter_course_table.php

// !!! SECURITY WARNING !!!
// This script is for a one-time database migration.
// It is designed to be run from a web browser and is protected by a secret key.
// AFTER YOU RUN THIS SUCCESSFULLY, YOU MUST DELETE THIS FILE.

$expected_secret = 'a1b2c3d4-e5f6-7890-1234-567890abcdef';
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
echo '<html><head><title>Alter Course Table</title></head><body><pre>';

browser_echo("Starting course table alteration...");

require_once __DIR__ . '/Database.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    browser_echo("ERROR: Database connection failed. Please check your credentials in config/Database.php");
    exit(1);
}

browser_echo("Database connection successful.");

try {
    browser_echo("Checking if 'course_code' column exists in 'courses' table...");
    $result = $db->query("SHOW COLUMNS FROM `courses` LIKE 'course_code'");
    $exists = $result->rowCount() > 0;

    if ($exists) {
        browser_echo("Column 'course_code' already exists.");
    } else {
        browser_echo("Attempting to add 'course_code' column...");
        $sql = "ALTER TABLE courses ADD COLUMN course_code VARCHAR(50) UNIQUE NOT NULL AFTER course_name";
        $db->exec($sql);
        browser_echo("Column 'course_code' added successfully.");
    }

} catch (PDOException $e) {
    browser_echo("ERROR altering table: " . $e->getMessage());
}

browser_echo("Course table alteration script finished.");

echo '</pre></body></html>';
