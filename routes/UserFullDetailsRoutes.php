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
    'GET /user-full-details/student/{student_number}/' => [
        'handler' => function ($student_number) use ($userFullDetailsController) {
            $userFullDetailsController->getRecordByStudentNumber($student_number);
        },
        'auth' => 'user'
    ],
    'POST /user-full-details/' => [
        'handler' => [$userFullDetailsController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /user-full-details/{id}/' => [
        'handler' => function ($id) use ($userFullDetailsController) {
            $userFullDetailsController->updateRecord($id);
        },
        'auth' => 'user'
    ],
    'DELETE /user-full-details/{id}/' => [
        'handler' => function ($id) use ($userFullDetailsController) {
            $userFullDetailsController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
