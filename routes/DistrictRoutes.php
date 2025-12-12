<?php

require_once __DIR__ . '/../controllers/DistrictController.php';

$pdo = Database::getInstance()->getConnection();
$districtController = new DistrictController($pdo);

return [
    // Get all districts, or districts by province
    'GET /districts' => [
        'handler' => [$districtController, 'getAllRecords'],
        'auth' => 'public'
    ],
    // Get a specific district by its ID
    'GET /districts/{id}' => [
        'handler' => [$districtController, 'getRecordById'],
        'auth' => 'public'
    ],
    // Create a new district
    'POST /districts' => [
        'handler' => [$districtController, 'createRecord'],
        'auth' => 'private'
    ],
    // Update an existing district
    'PUT /districts/{id}' => [
        'handler' => [$districtController, 'updateRecord'],
        'auth' => 'private'
    ],
    // Delete a district
    'DELETE /districts/{id}' => [
        'handler' => [$districtController, 'deleteRecord'],
        'auth' => 'private'
    ],
];
