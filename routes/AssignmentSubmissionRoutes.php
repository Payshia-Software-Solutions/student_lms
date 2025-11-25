<?php

require_once __DIR__ . '/../controllers/AssignmentSubmissionController.php';

// Load the existing FTP configuration
$ftp_config = require_once __DIR__ . '/../config/ftp.php';

$pdo = $GLOBALS['pdo'];
// Pass both the PDO connection and the loaded FTP config to the controller
$assignmentSubmissionController = new AssignmentSubmissionController($pdo, $ftp_config);

return [
    'GET /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'getAllRecords'],
        'auth' => 'user' // Assuming only logged-in users can see submissions
    ],
    'GET /assignment-submissions/{id}' => [
        'handler' => function ($params) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->getRecordById($params['id']);
        },
        'auth' => 'user'
    ],
    'POST /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'createRecord'],
        'auth' => 'user' // Students should be logged in to submit
    ],
    'PUT /assignment-submissions/{id}' => [
        'handler' => function ($params) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->updateRecord($params['id']);
        },
        'auth' => 'user' // Or 'admin' if only teachers can grade
    ],
    'DELETE /assignment-submissions/{id}' => [
        'handler' => function ($params) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->deleteRecord($params['id']);
        },
        'auth' => 'admin' // Assuming only admins can delete submissions
    ]
];
