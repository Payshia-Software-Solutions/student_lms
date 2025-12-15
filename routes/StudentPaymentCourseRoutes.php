<?php

require_once __DIR__ . '/../controllers/StudentPaymentCourseController.php';

$pdo = $GLOBALS['pdo'];
$studentPaymentCourseController = new StudentPaymentCourseController($pdo);

return [
    // Create a new student payment course - Private (JWT)
    'POST /student-payment-courses/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->createRecord();
        },
        'auth' => 'private'
    ],

    // Get all student payment courses - Private (JWT)
    'GET /student-payment-courses/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get student payment courses by filters - Private (JWT)
    'GET /student-payment-courses/filter/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getRecordsByFilters();
        },
        'auth' => 'private'
    ],

    // Get payment balance for a student - Private (JWT)
    'GET /student-payment-courses/balance/' => [
        'handler' => function () use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getPaymentBalance();
        },
        'auth' => 'private'
    ],

    // Get a student payment course by ID - Private (JWT)
    'GET /student-payment-courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Update a student payment course - Private (JWT)
    'PUT /student-payment-courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete a student payment course - Private (JWT)
    'DELETE /student-payment-courses/{id}/' => [
        'handler' => function ($id) use ($studentPaymentCourseController) {
            $studentPaymentCourseController->deleteRecord($id);
        },
        'auth' => 'private'
    ],
];
