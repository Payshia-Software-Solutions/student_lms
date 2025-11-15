<?php
// /config/setup.php

// !!! SECURITY WARNING !!!
// This script is for one-time database setup and is designed to be run from a web browser.
// It is protected by a secret key.
// AFTER YOU RUN THIS SUCCESSFULLY, YOU MUST DELETE THIS FILE.

$expected_secret = 'a917b4b4-36a5-4246-8c41-86194a281e74';
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
echo '<html><head><title>Database Setup</title></head><body><pre>';

browser_echo("Starting database setup...");

// Ensure all necessary files are included
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Province.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Enrollment.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    browser_echo("ERROR: Database connection failed. Please check your credentials in config/Database.php");
    exit(1);
}

browser_echo("Database connection successful.");

try {
    browser_echo("Creating tables if they don\'t exist...");

    User::createTable($db);
    Province::createTable($db);
    District::createTable($db);
    City::createTable($db);
    Student::createTable($db);
    Course::createTable($db);
    Enrollment::createTable($db);

    browser_echo("Tables created successfully.");

    browser_echo("Seeding data if tables are empty...");

    Province::seed($db);
    District::seed($db);
    City::seed($db);

    browser_echo("Data seeding completed.");

    browser_echo("Database setup finished successfully!");

} catch (Exception $e) {
    browser_echo("ERROR during setup: " . $e->getMessage());
    exit(1);
}

echo '</pre></body></html>';
