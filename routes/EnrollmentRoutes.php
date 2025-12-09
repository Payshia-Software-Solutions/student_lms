<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$enrollmentController = new EnrollmentController($GLOBALS['pdo']);

return [
    // Most specific routes first
    'GET /enrollments/student/{student_id}/approved/' => [
        'handler' => function($student_id) use ($enrollmentController) {
            $enrollmentController->getApprovedEnrollmentsForStudent($student_id);
        },
        'auth' => 'private'
    ],
    'GET /enrollments/student/{student_id}/' => [
        'handler' => function($student_id) use ($enrollmentController) {
            $enrollmentController->getEnrollmentsByStudent($student_id);
        },
        'auth' => 'private'
    ],
    'GET /enrollments/{id}/' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->getRecordById($id);
        },
        'auth' => 'private'
    ],
    // General collection route last among GETs
    'GET /enrollments/' => [
        'handler' => [$enrollmentController, 'getAllRecords'],
        'auth' => 'private'
    ],
    'POST /enrollments/' => [
        'handler' => [$enrollmentController, 'createRecord'],
        'auth' => 'private'
    ],
    'PUT /enrollments/{id}/' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->updateRecord($id);
        },
        'auth' => 'private'
    ],
    'DELETE /enrollments/{id}/' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->deleteRecord($id);
        },
        'auth' => 'private'
    ]
];
