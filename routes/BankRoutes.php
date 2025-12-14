<?php

require_once __DIR__ . '/../controllers/BankController.php';

$bankController = new BankController($GLOBALS['pdo']);

return [
    'GET /banks' => [
        'handler' => [$bankController, 'getAllRecords'],
        'auth' => 'public'
    ],
    'GET /banks/{id}' => [
        'handler' => [$bankController, 'getRecordById'],
        'auth' => 'public'
    ],
    'POST /banks' => [
        'handler' => [$bankController, 'createRecord'],
        'auth' => 'private'
    ],
    'PUT /banks/{id}' => [
        'handler' => [$bankController, 'updateRecord'],
        'auth' => 'private'
    ],
    'DELETE /banks/{id}' => [
        'handler' => [$bankController, 'deleteRecord'],
        'auth' => 'private'
    ],
];
