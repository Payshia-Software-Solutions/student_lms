<?php

require_once __DIR__ . '/../controllers/CompanyController.php';

$pdo = $GLOBALS['pdo'];
$companyController = new CompanyController($pdo);

return [
    // Get company details by a default ID of 1
    'GET /company/' => [
        'handler' => function () use ($companyController) {
            $companyController->getRecordById(1);
        },
        'auth' => 'public'
    ]
];
