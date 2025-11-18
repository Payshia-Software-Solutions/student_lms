<?php

require_once __DIR__ . '/../controllers/CourseBucketController.php';

$pdo = $GLOBALS['pdo'];
$courseBucketController = new CourseBucketController($pdo);

return [
    // Get all course buckets
    'GET /course_buckets/' => [
        'handler' => function () use ($courseBucketController) {
            $courseBucketController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a course bucket by ID
    'GET /course_buckets/{id}/' => [
        'handler' => function ($id) use ($courseBucketController) {
            $courseBucketController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Create a new course bucket
    'POST /course_buckets/' => [
        'handler' => function () use ($courseBucketController) {
            $courseBucketController->createRecord();
        },
        'auth' => 'private'
    ],

    // Update a course bucket
    'PUT /course_buckets/{id}/' => [
        'handler' => function ($id) use ($courseBucketController) {
            $courseBucketController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a course bucket
    'DELETE /course_buckets/{id}/' => [
        'handler' => function ($id) use ($courseBucketController) {
            $courseBucketController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
