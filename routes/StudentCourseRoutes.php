<?php

require_once __DIR__ . '/../controllers/StudentCourseController.php';

$pdo = $GLOBALS['pdo'];
$studentCourseController = new StudentCourseController($pdo);

return [
    // Create student_course table
    'GET /student-courses/create-table/' => [
        'handler' => function () use ($studentCourseController) {
            $studentCourseController->createStudentCourseTable();
        },
        'auth' => 'private'
    ],

    // Create a new student course entry - Private (JWT)
    'POST /student-courses/' => [
        'handler' => function () use ($studentCourseController) {
            $studentCourseController->createRecord();
        },
        'auth' => 'private'
    ],

    // Get all student course entries - Private (JWT)
    'GET /student-courses/' => [
        'handler' => function () use ($studentCourseController) {
            $studentCourseController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a student course entry by ID - Private (JWT)
    'GET /student-courses/{id}/' => [
        'handler' => function ($id) use ($studentCourseController) {
            $studentCourseController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Update a student course entry - Private (JWT)
    'PUT /student-courses/{id}/' => [
        'handler' => function ($id) use ($studentCourseController) {
            $studentCourseController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a student course entry - Private (JWT)
    'DELETE /student-courses/{id}/' => [
        'handler' => function ($id) use ($studentCourseController) {
            $studentCourseController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
