<?php

require_once __DIR__ . '/../controllers/PaymentRequestController.php';

$pdo = $GLOBALS['pdo'];
$paymentRequestController = new PaymentRequestController($pdo);

return [
    // Get all records
    'GET /payment_requests/' => [
        'handler' => function () use ($paymentRequestController) {
            $paymentRequestController->getAllRecords();
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
