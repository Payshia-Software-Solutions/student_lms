<?php

require_once __DIR__ . '/../controllers/EnrollmentController.php';

$pdo = $GLOBALS['pdo'];
$enrollmentController = new EnrollmentController($pdo);

return [
    // Create a new enrollment - Private (JWT)
    'POST /enrollments/' => [
        'handler' => function () use ($enrollmentController) {
            $enrollmentController->createRecord();
        },
        'auth' => 'private'
    ],

    // Get all enrollments - Private (JWT)
    'GET /enrollments/' => [
        'handler' => function () use ($enrollmentController) {
            $enrollmentController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get an enrollment by ID - Private (JWT)
    'GET /enrollments/{id}/' => [
        'handler' => function ($id) use ($enrollmentController) {
            $enrollmentController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Update an enrollment - Private (JWT)
    'PUT /enrollments/{id}/' => [
        'handler' => function ($id) use ($enrollmentController) {
            $enrollmentController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete an enrollment (soft delete) - Private (JWT)
    'DELETE /enrollments/{id}/' => [
        'handler' => function ($id) use ($enrollmentController) {
            $enrollmentController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
