<?php

require_once __DIR__ . '/../controllers/AssignmentController.php';

$ftp_config = require __DIR__ . '/../config/ftp.php';
$pdo = $GLOBALS['pdo'];
$assignmentController = new AssignmentController($pdo, $ftp_config);

return [
    'GET /assignments/' => [
        'handler' => [$assignmentController, 'getAllRecords'],
        'auth' => 'user'
    ],
    'GET /assignments/{id}/' => [
        'handler' => function ($id) use ($assignmentController) {
            $assignmentController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'POST /assignments/' => [
        'handler' => [$assignmentController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /assignments/{id}/' => [
        'handler' => function ($id) use ($assignmentController) {
            $assignmentController->updateRecord($id);
        },
        'auth' => 'user'
    ],
    'DELETE /assignments/{id}/' => [
        'handler' => function ($id) use ($assignmentController) {
            $assignmentController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
