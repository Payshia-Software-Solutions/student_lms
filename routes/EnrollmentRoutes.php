<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$enrollmentController = new EnrollmentController($GLOBALS['pdo']);

return [
    'GET /enrollments/' => [
        'handler' => function() use ($enrollmentController) {
            if (isset($_GET['enroll_status'])) {
                $enrollmentController->getEnrollmentsByStatus($_GET['enroll_status']);
            } else if (isset($_GET['course_id'])) {
                $enrollmentController->getEnrollmentsByCourse($_GET['course_id']);
            } else {
                $enrollmentController->getEnrollments();
            }
        },
        'auth' => 'private'
    ],
    'GET /enrollments/student/{student_id}/approved' => [
        'handler' => function($params) use ($enrollmentController) {
            $enrollmentController->getApprovedEnrollmentsForStudent($params['student_id']);
        },
        'auth' => 'private'
    ],
    'GET /enrollments/{id}' => [
        'handler' => function($params) use ($enrollmentController) {
            $enrollmentController->getRecordById($params['id']);
        },
        'auth' => 'private'
    ],
    'POST /enrollments/' => [
        'handler' => [$enrollmentController, 'createRecord'],
        'auth' => 'private'
    ],
    'PUT /enrollments/{id}' => [
        'handler' => function($params) use ($enrollmentController) {
            $enrollmentController->updateRecord($params['id']);
        },
        'auth' => 'private'
    ],
    'DELETE /enrollments/{id}' => [
        'handler' => function($params) use ($enrollmentController) {
            $enrollmentController->deleteRecord($params['id']);
        },
        'auth' => 'private'
    ]
];
