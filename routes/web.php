<?php
// Set CORS headers for every response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Handle OPTIONS requests (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}


ini_set('memory_limit', '256M');
// Report all PHP errors
error_reporting(E_ALL);
// Display errors in the browser (for development)
ini_set('display_errors', 1);

// JWT Middleware
require_once __DIR__ . '/../utils/JwtHelper.php';
$publicRoutes = [
   

];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
// Remove query string for route matching
if (strpos($requestUri, '?') !== false) {
    $requestUri = strtok($requestUri, '?');
}
// Normalize route for matching (allow /erp_server/users/login and /users/login)
$normalizedRoute = $requestMethod . ' ' . preg_replace('#^/erp_server#', '', $requestUri);
if (!in_array($normalizedRoute, $publicRoutes)) {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid Authorization header']);
        exit();
    }
    $token = $matches[1];
    $jwtPayload = JwtHelper::validateToken($token);
    if (!$jwtPayload) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        exit();
    }
    // Optionally, set $jwtPayload to global for use in controllers
    $GLOBALS['jwtPayload'] = $jwtPayload;
}

$UserRoutes = require_once __DIR__ . '/UserRoutes.php';


// Combine all routes
$routes = array_merge(
    $UserRoutes

 
);

// Define the home route with trailing slash
$routes['GET /'] = function () {
    // Serve the index.html file
    readfile('../views/index.html');
};

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);  // Get only the path, not query parameters

// Ensure URI always has a trailing slash
if (substr($uri, -1) !== '/') {
    // $uri .= '/';
}

// Determine if the application is running on localhost
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Adjust URI if needed (only on localhost)
    $uri = str_replace('lms_server/', '', $uri);
} else {
    // Adjust URI if needed (if using a subdirectory)
    $uri = $uri;
}

// Set the header for JSON responses, except for HTML pages
if ($uri !== '/') {
    header('Content-Type: application/json');
}

// Debugging
error_log("Method: $method");
error_log("URI: $uri");

// Define a generic regex pattern for routes with placeholders like {id}, {username}, etc.
$routeRegexPattern = "#\{[a-zA-Z0-9_]+\}#"; // Matches anything inside {}

// Route matching
foreach ($routes as $route => $handler) {
    list($routeMethod, $routeUri) = explode(' ', $route, 2);

    // Replace all placeholders like {id}, {username}, etc. with a generic regex that matches alphanumeric strings
    $routeRegex = preg_replace($routeRegexPattern, '([a-zA-Z0-9_\-]+)', $routeUri);
    $routeRegex = "#^" . rtrim($routeRegex, '/') . "/?$#";

    error_log("Checking route: $routeRegex");

    // Check if the route matches the request
    if ($method === $routeMethod && preg_match($routeRegex, $uri, $matches)) {
        array_shift($matches); // Remove the full match
        error_log("Route matched: $route");

        // Call the route handler with dynamic parameters
        call_user_func_array($handler, $matches);
        exit;
    }
}

// Default 404 response
header("HTTP/1.1 404 Not Found");
echo json_encode(['error' => 'Route not found']);
