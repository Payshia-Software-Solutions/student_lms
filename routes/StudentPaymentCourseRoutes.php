<?php

require_once __DIR__ . '/../controllers/StudentPaymentCourseController.php';

$pdo = $GLOBALS['pdo'];
$studentPaymentCourseController = new StudentPaymentCourseController($pdo);

return [
    // Get all records
    'GET /student_payment_courses/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a record by ID
    'GET /student_payment_courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Create a new record
    'POST /student_payment_courses/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->createRecord();
        },
        'auth' => 'private'
    ],

    // Update a record
    'PUT /student_payment_courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a record
    'DELETE /student_payment_courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
