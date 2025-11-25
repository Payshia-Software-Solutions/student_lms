<?php

require_once __DIR__ . '/../controllers/AssignmentSubmissionController.php';

// Load the existing FTP configuration using 'require' to ensure it's loaded every time
$ftp_config = require __DIR__ . '/../config/ftp.php';

$pdo = $GLOBALS['pdo'];
// Pass both the PDO connection and the loaded FTP config to the controller
$assignmentSubmissionController = new AssignmentSubmissionController($pdo, $ftp_config);

return [
    // Route for filtering submissions. Keep it before the /:id route.
    'GET /assignment-submissions/filter/' => [
        'handler' => [$assignmentSubmissionController, 'getRecordsByFilter'],
        'auth' => 'user' // Or 'admin' if this is a privileged action
    ],
    'GET /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'getAllRecords'],
        'auth' => 'user' // Assuming only logged-in users can see submissions
    ],

    // **FIXED**: Corrected handler to properly receive the ID from the URL
    'GET /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'POST /assignment-submissions/' => [
        'handler' => [$assignmentSubmissionController, 'createRecord'],
        'auth' => 'user' // Students should be logged in to submit
    ],

    // **FIXED**: Corrected handler to properly receive the ID from the URL
    'PUT /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->updateRecord($id);
        },
        'auth' => 'user' // Or 'admin' if only teachers can grade
    ],

    // **FIXED**: Corrected handler to properly receive the ID from the URL
    'DELETE /assignment-submissions/{id}/' => [
        'handler' => function ($id) use ($assignmentSubmissionController) {
            $assignmentSubmissionController->deleteRecord($id);
        },
        'auth' => 'admin' // Assuming only admins can delete submissions
    ]
];
