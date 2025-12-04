<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/OrderableItemController.php';
require_once __DIR__ . '/controllers/StudentOrderController.php';

$database = new Database();
$pdo = $database->getConnection();
$ftp_config = [
    'server' => 'qa-lms-server.payshia.com',
    'username' => 'lms-user',
    'password' => 'lms-user'
];

$orderableItemController = new OrderableItemController($pdo, $ftp_config);
$studentOrderController = new StudentOrderController($pdo);

$request_method = $_SERVER["REQUEST_METHOD"];
$request_uri = $_SERVER['REQUEST_URI'];

// Basic routing
if ($request_method == 'GET' && preg_match('/^\/orderable-items\/?$/', $request_uri)) {
    $orderableItemController->getAllRecords();
} elseif ($request_method == 'GET' && preg_match('/^\/orderable-items\/by-course\/?$/', $request_uri)) {
    $orderableItemController->getRecordsByCourse();
} elseif ($request_method == 'GET' && preg_match('/^\/orderable-items\/(\d+)\/?$/', $request_uri, $matches)) {
    $orderableItemController->getRecordById($matches[1]);
} elseif ($request_method == 'POST' && preg_match('/^\/orderable-items\/?$/', $request_uri)) {
    $orderableItemController->createRecord();
} elseif ($request_method == 'PUT' && preg_match('/^\/orderable-items\/(\d+)\/?$/', $request_uri, $matches)) {
    $orderableItemController->updateRecord($matches[1]);
} elseif ($request_method == 'DELETE' && preg_match('/^\/orderable-items\/(\d+)\/?$/', $request_uri, $matches)) {
    $orderableItemController->deleteRecord($matches[1]);
} elseif ($request_method == 'GET' && preg_match('/^\/student-orders\/?$/', $request_uri)) {
    $studentOrderController->getAllRecords();
} elseif ($request_method == 'GET' && preg_match('/^\/student-orders\/(\d+)\/?$/', $request_uri, $matches)) {
    $studentOrderController->getRecordById($matches[1]);
} elseif ($request_method == 'POST' && preg_match('/^\/student-orders\/?$/', $request_uri)) {
    $studentOrderController->createRecord();
} elseif ($request_method == 'PUT' && preg_match('/^\/student-orders\/(\d+)\/?$/', $request_uri, $matches)) {
    $studentOrderController->updateRecord($matches[1]);
} elseif ($request_method == 'DELETE' && preg_match('/^\/student-orders\/(\d+)\/?$/', $request_uri, $matches)) {
    $studentOrderController->deleteRecord($matches[1]);
} else {
    header("HTTP/1.0 404 Not Found");
    echo '404 Not Found';
}
