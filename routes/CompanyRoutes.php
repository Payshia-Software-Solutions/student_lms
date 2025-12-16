<?php

require_once __DIR__ . '/../controllers/CompanyController.php';

$pdo = $GLOBALS['pdo'];
$companyController = new CompanyController($pdo);

return [
    // Get all companies
    'GET /company/' => [
        'handler' => [$companyController, 'getAllRecords'],
        'auth' => 'public'
    ],
    // Get a single company by ID
    'GET /company/{id}' => [
        'handler' => function ($params) use ($companyController) {
            $companyController->getRecordById($params['id']);
        },
        'auth' => 'public'
    ],
    // Create a new company
    'POST /company/create' => [
        'handler' => [$companyController, 'createRecord'],
        'auth' => 'private'
    ],
    // Update an existing company
    'PUT /company/{id}/update' => [
        'handler' => function ($params) use ($companyController) {
            $companyController->updateRecord($params['id']);
        },
        'auth' => 'private'
    ],
    // Delete a company
    'DELETE /company/{id}/delete' => [
        'handler' => function ($params) use ($companyController) {
            $companyController->deleteRecord($params['id']);
        },
        'auth' => 'private'
    ],
];
