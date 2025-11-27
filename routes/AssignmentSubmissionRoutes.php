<?php

require_once __DIR__ . '/../controllers/AssignmentSubmissionController.php';

$ftp_config = require __DIR__ . '/../config/ftp.php';
$pdo = $GLOBALS['pdo'];
$assignmentSubmissionController = new AssignmentSubmissionController($pdo, $ftp_config);

return [
    'GET /assignment-submissions/filter/' => [
        'handler' => [$assignmentSubmissionController, 'getRecordsByFilter'],
        'auth' => 'user'
    ],
    'GET /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'getAllRecords'],
        'auth' => 'user'
    ],
    'GET /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'POST /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'createRecord'],
        'auth' => 'user'
    ],
    'POST /assignment-submissions/{id}/update-file/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->updateSubmissionFile($id);
        },
        'auth' => 'user'
    ],
    'PUT /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->updateRecord($id);
        },
        'auth' => 'user'
    ],
    'PUT /assignment-submissions/update-status/' => [
        'handler' => [$assignmentSubmissionController, 'updateSubmissionStatus'],
        'auth' => 'user'
    ],
    'DELETE /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
