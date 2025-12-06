<?php

require_once __DIR__ . '/../controllers/PaymentRequestController.php';

// Load the existing FTP configuration using 'require' to ensure it's loaded every time
$ftp_config = require __DIR__ . '/../config/ftp.php';

$pdo = $GLOBALS['pdo'];
// Pass both the PDO connection and the loaded FTP config to the controller
$paymentRequestController = new PaymentRequestController($pdo, $ftp_config);

return [
    // Get all records
    'GET /payment_requests/' => [
        'handler' => function () use ($paymentRequestController) {
            $paymentRequestController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get records by filter
    'GET /payment_requests/filter/' => [
        'handler' => function () use ($paymentRequestController) {
            $paymentRequestController->getRecordsByFilter();
        },
        'auth' => 'private'
    ],

    // Get a record by ID
    'GET /payment_requests/{id}/' => [
        'handler' => function ($id) use ($paymentRequestController) {
            $paymentRequestController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Create a new record
    'POST /payment_requests/' => [
        'handler' => function () use ($paymentRequestController) {
            $paymentRequestController->createRecord();
        },
        'auth' => 'private'
    ],

    // Update a record
    'PUT /payment_requests/{id}/' => [
        'handler' => function ($id) use ($paymentRequestController) {
            $paymentRequestController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a record
    'DELETE /payment_requests/{id}/' => [
        'handler' => function ($id) use ($paymentRequestController) {
            $paymentRequestController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
