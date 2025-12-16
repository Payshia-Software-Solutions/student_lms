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
        'handler' => function ($id) use ($companyController) {
            $companyController->getRecordById($id);
        },
        'auth' => 'public'
    ],
    // Create a new company
    'POST /company/' => [
        'handler' => [$companyController, 'createRecord'],
        'auth' => 'private'
    ],
    // Update an existing company
    'POST /company/{id}' => [
        'handler' => function ($id) use ($companyController) {
            $companyController->updateRecord($id);
        },
        'auth' => 'private'
    ],
    // Delete a company
    'DELETE /company/{id}/delete' => [
        'handler' => function ($id) use ($companyController) {
            $companyController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
