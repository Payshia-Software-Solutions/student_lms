<?php

require_once __DIR__ . '/../controllers/StudentController.php';

$pdo = $GLOBALS['pdo'];
$studentController = new StudentController($pdo);

return [
    // Create a new student - Private (JWT)
    'POST /students/' => [
        'handler' => function () use ($studentController) {
            $studentController->createRecord();
        },
        'auth' => 'private'
    ],

    // Get all students - Private (JWT)
    'GET /students/' => [
        'handler' => function () use ($studentController) {
            $studentController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a student by ID - Private (JWT)
    'GET /students/{id}/' => [
        'handler' => function ($id) use ($studentController) {
            $studentController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Update student - Private (JWT)
    'PUT /students/{id}/' => [
        'handler' => function ($id) use ($studentController) {
            $studentController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete student (soft delete) - Private (JWT)
    'DELETE /students/{id}/' => [
        'handler' => function ($id) use ($studentController) {
            $studentController->deleteRecord($id);
        },
        'auth' => 'private'
    ],

    // Get all courses for a student - Private (JWT)
    'GET /students/{id}/courses/' => [
        'handler' => function ($id) use ($studentController) {
            $studentController->getStudentEnrollments($id);
        },
        'auth' => 'private'
    ],
];
