<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$enrollmentController = new EnrollmentController($GLOBALS['pdo']);

return [
    'GET /enrollments/' => [
        'handler' => function() use ($enrollmentController) {
            $enrollmentController->handleRequest('GET', 'enrollments', null);
        },
        'auth' => 'private'
    ],
    'GET /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('GET', 'enrollments', $id);
        },
        'auth' => 'private'
    ],
    'POST /enrollments/' => [
        'handler' => function() use ($enrollmentController) {
            $enrollmentController->handleRequest('POST', 'enrollments', null);
        },
        'auth' => 'private'
    ],
    'PUT /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('PUT', 'enrollments', $id);
        },
        'auth' => 'private'
    ],
    'DELETE /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('DELETE', 'enrollments', $id);
        },
        'auth' => 'private'
    ]
];
