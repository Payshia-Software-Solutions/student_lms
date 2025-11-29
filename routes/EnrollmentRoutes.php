<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$enrollmentController = new EnrollmentController($GLOBALS['pdo']);

return [
    'GET /enrollments/' => [
        'handler' => [$enrollmentController, 'getEnrollments'],
        'auth' => 'private'
    ],
    'GET /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->getRecordById($id);
        },
        'auth' => 'private'
    ],
    'POST /enrollments/' => [
        'handler' => [$enrollmentController, 'createRecord'],
        'auth' => 'private'
    ],
    'PUT /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->updateRecord($id);
        },
        'auth' => 'private'
    ],
    'DELETE /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->deleteRecord($id);
        },
        'auth' => 'private'
    ]
];
