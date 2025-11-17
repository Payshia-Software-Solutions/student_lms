<?php
// /config/alter_user_table.php

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
echo '<html><head><title>Alter User Table</title></head><body><pre>';

browser_echo("Starting user table alteration...");

require_once __DIR__ . '/Database.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    browser_echo("ERROR: Database connection failed. Please check your credentials in config/Database.php");
    exit(1);
}

browser_echo("Database connection successful.");

try {
    browser_echo("Checking if 'user_status' column exists...");
    $result = $db->query("SHOW COLUMNS FROM `users` LIKE 'user_status'");
    $exists = $result->rowCount() > 0;

    if ($exists) {
        browser_echo("Column 'user_status' already exists.");
    } else {
        browser_echo("Attempting to add 'user_status' column...");
        $sql = "ALTER TABLE users ADD COLUMN user_status ENUM('student', 'admin') NOT NULL DEFAULT 'student' AFTER nic";
        $db->exec($sql);
        browser_echo("Column 'user_status' added successfully.");
    }

} catch (PDOException $e) {
    browser_echo("ERROR altering table: " . $e->getMessage());
}

browser_echo("User table alteration script finished.");

echo '</pre></body></html>';
