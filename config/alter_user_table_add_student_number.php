<?php
// /config/alter_user_table_add_student_number.php

// !!! SECURITY WARNING !!!
// This script is for a one-time database migration and is protected by a secret key.
// AFTER YOU RUN THIS SUCCESSFULLY, YOU MUST DELETE THIS FILE.

$expected_secret = 'a1b2c3d4-e5f6-7890-1234-567890abcdef';
$provided_secret = isset($_GET['secret']) ? $_GET['secret'] : '';

if ($provided_secret !== $expected_secret) {
    header("HTTP/1.1 403 Forbidden");
    echo "<h1>403 Forbidden</h1><p>Invalid or missing secret key.</p>";
    exit;
}

function browser_echo($message) {
    echo htmlspecialchars($message) . "<br>" . PHP_EOL;
    flush();
}

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Alter User Table (Add Student Number)</title></head><body><pre>';

browser_echo("Starting user table alteration...");

require_once __DIR__ . '/Database.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    browser_echo("ERROR: Database connection failed.");
    exit(1);
}

browser_echo("Database connection successful.");

try {
    browser_echo("Checking if 'student_number' column exists...");
    $result = $db->query("SHOW COLUMNS FROM `users` LIKE 'student_number'");
    if ($result->rowCount() > 0) {
        browser_echo("Column 'student_number' already exists.");
    } else {
        browser_echo("Attempting to add 'student_number' column...");
        // Add the new column. It's set to UNIQUE to ensure no duplicates.
        $sql = "ALTER TABLE users ADD COLUMN student_number VARCHAR(20) UNIQUE NULL AFTER user_status";
        $db->exec($sql);
        browser_echo("Column 'student_number' added successfully.");
    }
} catch (PDOException $e) {
    browser_echo("ERROR altering table: " . $e->getMessage());
}

browser_echo("User table alteration script finished.");

echo '</pre></body></html>';
