<?php

require_once __DIR__ . '/../controllers/UserFullDetailsController.php';

$pdo = $GLOBALS['pdo'];
$userFullDetailsController = new UserFullDetailsController($pdo);

return [
    'GET /user-full-details/' => [
        'handler' => [$userFullDetailsController, 'getAllRecords'],
        'auth' => 'admin'
    ],
    'GET /user-full-details/{id}/' => [
        'handler' => function ($id) use ($userFullDetailsController) {
            $userFullDetailsController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'GET /user-full-details/student/' => [
        'handler' => [$userFullDetailsController, 'getRecordByStudentNumberQuery'],
        'auth' => 'user'
    ],
    'GET /user-full-details/student/courses/' => [
        'handler' => [$userFullDetailsController, 'getUserWithCourseDetails'],
        'auth' => 'user'
    ],
    'POST /user-full-details/' => [
        'handler' => [$userFullDetailsController, 'createRecord'],
        'auth' => 'admin'
    ],
    'PUT /user-full-details/{id}/' => [
        'handler' => function ($id) use ($userFullDetailsController) {
            $userFullDetailsController->updateRecord($id);
        },
        'auth' => 'admin'
    ],
    'DELETE /user-full-details/{id}/' => [
        'handler' => function ($id) use ($userFullDetailsController) {
            $userFullDetailsController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
