<?php
// web.php

require_once __DIR__ . '/../middleware/CORSMiddleware.php';
require_once __DIR__ . '/../middleware/JwtAuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyAuthMiddleware.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/Province.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/City.php';

CORSMiddleware::handle();

// --- Error and Exception Handling ---

// Don't display errors to the user in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Set a custom exception handler
set_exception_handler(function ($exception) {
    // Log the error
    error_log(
        "Uncaught Exception: " . $exception->getMessage() . 
        " in " . $exception->getFile() . 
        " on line " . $exception->getLine()
    );

    // Send a generic error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An internal server error occurred.'
    ]);
    exit;
});

// Set a custom error handler for notices, warnings, etc.
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    // Log the error
    error_log(
        "Error: [$severity] $message in $file on line $line"
    );

    // Continue with the standard PHP error handler if display_errors is on
    // But since we turned it off, this effectively just logs the error.
    return false; // Let PHP's internal error handler also run (which will log it)
});


// --- End of Error Handling ---


ini_set('memory_limit', '256M');

// Create tables if they don't exist
$database = new Database();
$db = $database->getConnection();
Student::createTable($db);
Course::createTable($db);
Enrollment::createTable($db);
Province::createTable($db);
District::createTable($db);
City::createTable($db);

$UserRoutes = require_once __DIR__ . '/UserRoutes.php';
$StudentRoutes = require_once __DIR__ . '/StudentRoutes.php';
$CourseRoutes = require_once __DIR__ . '/CourseRoutes.php';
$EnrollmentRoutes = require_once __DIR__ . '/EnrollmentRoutes.php';
$LocationRoutes = require_once __DIR__ . '/LocationRoutes.php';

// Combine all routes
$routes = array_merge(
    $UserRoutes,
    $StudentRoutes,
    $CourseRoutes,
    $EnrollmentRoutes,
    $LocationRoutes,
    [
        'GET /ping/' => [
            'handler' => function () {
                echo json_encode(['status' => 'success', 'message' => 'pong']);
            },
            'auth' => 'public'
        ]
    ]
);

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// If the application is in a subdirectory named 'student_lms', remove it from the URI
$basePath = '/student_lms';
if (substr($uri, 0, strlen($basePath)) === $basePath) {
    $uri = substr($uri, strlen($basePath));
}

// Define the home route with trailing slash
$routes['GET /'] = [
    'handler' => function () {
        // Serve the index.html file
        readfile('./views/index.html');
    },
    'auth' => 'none' // No authentication needed
];

// Route matching and authentication
foreach ($routes as $route => $details) {
    list($routeMethod, $routeUri) = explode(' ', $route, 2);
    $handler = $details['handler'];
    $authType = $details['auth'];

    $routeRegex = "#^" . preg_replace('#\\{[a-zA-Z0-9_]+\\}#', '([a-zA-Z0-9_\\-]+)', rtrim($routeUri, '/')) . "/?$#";

    if ($method === $routeMethod && preg_match($routeRegex, $uri, $matches)) {
        array_shift($matches); // Remove the full match

        // Handle authentication
        if ($authType === 'private') {
            JwtAuthMiddleware::handle();
        } elseif ($authType === 'public') {
            ApiKeyAuthMiddleware::handle();
        }

        // Set the header for JSON responses, except for HTML pages
        if ($uri !== '/') {
            header('Content-Type: application/json');
        }

        call_user_func_array($handler, $matches);
        exit;
    }
}

// Default 404 response
header("HTTP/1.1 404 Not Found");
echo json_encode(['error' => 'Route not found']);
