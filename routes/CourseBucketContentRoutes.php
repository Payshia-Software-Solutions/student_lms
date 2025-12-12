<?php

require_once __DIR__ . '/../controllers/CourseBucketContentController.php';

$ftp_config = require __DIR__ . '/../config/ftp.php';
$pdo = $GLOBALS['pdo'];
$courseBucketContentController = new CourseBucketContentController($pdo, $ftp_config);

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
    'POST /course-bucket-contents/' => [
        'handler' => [$courseBucketContentController, 'createRecord'],
        'auth' => 'user'
    ],
    'POST /course-bucket-contents/{id}/' => [
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
