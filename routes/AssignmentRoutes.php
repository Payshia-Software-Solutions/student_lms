<?php

require_once __DIR__ . '/../controllers/AssignmentController.php';

$pdo = $GLOBALS['pdo'];
$assignmentController = new AssignmentController($pdo);

return [
    'GET /assignments/' => [
        'handler' => [$assignmentController, 'getAllRecords'],
        'auth' => 'public'
    ],
    'GET /assignments/{id}' => [
        'handler' => function ($params) use ($assignmentController) {
            $assignmentController->getRecordById($params['id']);
        },
        'auth' => 'public'
    ],
    'POST /assignments/' => [
        'handler' => [$assignmentController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /assignments/{id}' => [
        'handler' => function ($params) use ($assignmentController) {
            $assignmentController->updateRecord($params['id']);
        },
        'auth' => 'user'
    ],
    'DELETE /assignments/{id}' => [
        'handler' => function ($params) use ($assignmentController) {
            $assignmentController->deleteRecord($params['id']);
        },
        'auth' => 'user'
    ]
];
