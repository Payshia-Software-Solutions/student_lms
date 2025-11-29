<?php

require_once __DIR__ . '/../controllers/CourseController.php';

$pdo = $GLOBALS['pdo'];
$courseController = new CourseController($pdo);

return [
    // Create a new course - Private (JWT)
    'POST /courses/' => [
        'handler' => function () use ($courseController) {
            $courseController->createRecord();
        },
        'auth' => 'private'
    ],

    // Get all courses - Private (JWT)
    'GET /courses/' => [
        'handler' => function () use ($courseController) {
            $courseController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a course by ID - Private (JWT)
    'GET /courses/{id}/' => [
        'handler' => function ($id) use ($courseController) {
            $courseController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Get a course with all its buckets and content using a query parameter
    'GET /courses/full/details/' => [
        'handler' => [$courseController, 'getCourseWithDetails'],
        'auth' => 'private' // Or 'user' depending on your auth scheme
    ],

    // Update course - Private (JWT)
    'PUT /courses/{id}/' => [
        'handler' => function ($id) use ($courseController) {
            $courseController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete course (soft delete) - Private (JWT)
    'DELETE /courses/{id}/' => [
        'handler' => function ($id) use ($courseController) {
            $courseController->deleteRecord($id);
        },
        'auth' => 'private'
    ],

    // Get all students for a course - Private (JWT)
    'GET /courses/{id}/students/' => [
        'handler' => function ($id) use ($courseController) {
            $courseController->getCourseEnrollments($id);
        },
        'auth' => 'private'
    ]
];
