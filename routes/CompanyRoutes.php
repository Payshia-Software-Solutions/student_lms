<?php

require_once __DIR__ . '/../controllers/CompanyController.php';

$pdo = $GLOBALS['pdo'];
$companyController = new CompanyController($pdo);

return [
    // Get company details
    'GET /company/' => [
        'handler' => function () use ($companyController) {
            $companyController->getRecord();
        },
        'auth' => 'public'
    ]
];
