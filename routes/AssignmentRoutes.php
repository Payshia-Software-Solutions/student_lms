<?php

require_once __DIR__ . '/../controllers/AssignmentController.php';

$pdo = $GLOBALS['pdo'];
$ftp_config = $GLOBALS['ftp_config'];
$assignmentController = new AssignmentController($pdo, $ftp_config);

return [
    'GET /assignments/' => [
        'handler' => [$assignmentController, 'getAllRecords'],
        'auth' => 'user'
    ],
    'GET /assignments/full/submissions/' => [
        'handler' => [$assignmentController, 'getAssignmentsWithSubmissions'],
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
        'auth' => 'admin'
    ],
    'POST /assignments/{id}/' => [
        'handler' => function ($id) use ($assignmentController) {
            $assignmentController->updateRecord($id);
        },
        'auth' => 'admin'
    ],
    'DELETE /assignments/{id}/' => [
        'handler' => function ($id) use ($assignmentController) {
            $assignmentController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
