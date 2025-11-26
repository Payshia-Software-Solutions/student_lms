<?php

require_once __DIR__ . '/../controllers/CourseBucketContentController.php';

$pdo = $GLOBALS['pdo'];
$courseBucketContentController = new CourseBucketContentController($pdo);

return [
    'GET /course-bucket-contents/' => [
        'handler' => [$courseBucketContentController, 'getAllRecords'],
        'auth' => 'user'
    ],
    'GET /course-bucket-contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    // **NEW**: Route to get all content for a specific course bucket
    'GET /course-bucket-contents/bucket/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->getRecordsByCourseBucketId($id);
        },
        'auth' => 'user'
    ],
    'POST /course-bucket-contents/' => [
        'handler' => [$courseBucketContentController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /course-bucket-contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->updateRecord($id);
        },
        'auth' => 'user'
    ],
    'DELETE /course-bucket-contents/{id}/' => [
        'handler' => function ($id) use ($courseBucketContentController) {
            $courseBucketContentController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
