<?php

require_once __DIR__ . '/../controllers/CityController.php';

$pdo = Database::getInstance()->getConnection();
$cityController = new CityController($pdo);

return [
    // Get all cities, or cities by district
    'GET /cities' => [
        'handler' => [$cityController, 'getAllRecords'],
        'auth' => 'public'
    ],
    // Get a specific city by its ID
    'GET /cities/{id}' => [
        'handler' => [$cityController, 'getRecordById'],
        'auth' => 'public'
    ],
    // Create a new city
    'POST /cities' => [
        'handler' => [$cityController, 'createRecord'],
        'auth' => 'private'
    ],
    // Update an existing city
    'PUT /cities/{id}' => [
        'handler' => [$cityController, 'updateRecord'],
        'auth' => 'private'
    ],
    // Delete a city
    'DELETE /cities/{id}' => [
        'handler' => [$cityController, 'deleteRecord'],
        'auth' => 'private'
    ],
];
