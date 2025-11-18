<?php

require_once __DIR__ . '/../controllers/CourseBucketContentController.php';

$pdo = $GLOBALS['pdo'];
$courseBucketContentController = new CourseBucketContentController($pdo);

return [
    // Get all course bucket contents
    'GET /course_bucket_contents/' => [
        'handler' => function () use ($courseBucketContentController) {
            $courseBucketContentController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a course bucket content by ID
    'GET /course_bucket_contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Create a new course bucket content
    'POST /course_bucket_contents/' => [
        'handler' => function () use ($courseBucketContentController) {
            $courseBucketContentController->createRecord();
        },
        'auth' => 'private'
    ],

    // Update a course bucket content
    'PUT /course_bucket_contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a course bucket content
    'DELETE /course_bucket_contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
