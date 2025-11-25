<?php

require_once __DIR__ . '/../controllers/AssignmentSubmissionController.php';

$pdo = $GLOBALS['pdo'];
$assignmentSubmissionController = new AssignmentSubmissionController($pdo);

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
