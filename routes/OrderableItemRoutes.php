<?php

require_once __DIR__ . '/../controllers/OrderableItemController.php';

$ftp_config = require __DIR__ . '/../config/ftp.php';
$pdo = $GLOBALS['pdo'];
$orderableItemController = new OrderableItemController($pdo, $ftp_config);

return [
    'GET /orderable-items/' => [
        'handler' => [$orderableItemController, 'getAllRecords'],
        'auth' => 'user'
    ],
    'GET /orderable-items/{id}/' => [
        'handler' => function ($id) use ($orderableItemController) {
            $orderableItemController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'POST /orderable-items/' => [
        'handler' => [$orderableItemController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /orderable-items/{id}/' => [
        'handler' => function ($id) use ($orderableItemController) {
            $orderableItemController->updateRecord($id);
        },
        'auth' => 'user'
    ],
    'DELETE /orderable-items/{id}/' => [
        'handler' => function ($id) use ($orderableItemController) {
            $orderableItemController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
