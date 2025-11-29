<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$enrollmentController = new EnrollmentController($GLOBALS['pdo']);

return [
    'GET /enrollments/' => [
        'handler' => function() use ($enrollmentController) {
            $enrollmentController->handleRequest('GET', null);
        },
        'auth' => 'private'
    ],
    'GET /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('GET', $id);
        },
        'auth' => 'private'
    ],
    'POST /enrollments/' => [
        'handler' => function() use ($enrollmentController) {
            $enrollmentController->handleRequest('POST', null);
        },
        'auth' => 'private'
    ],
    'PUT /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('PUT', $id);
        },
        'auth' => 'private'
    ],
    'DELETE /enrollments/{id}' => [
        'handler' => function($id) use ($enrollmentController) {
            $enrollmentController->handleRequest('DELETE', $id);
        },
        'auth' => 'private'
    ]
];
