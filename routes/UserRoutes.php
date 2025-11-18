<?php

require_once __DIR__ . '/../controllers/UserController.php';

$pdo = $GLOBALS['pdo'];
$userController = new UserController($pdo);

return [
    // User Login - No Auth
    'POST /login/' => [
        'handler' => function () use ($userController) {
            $userController->login();
        },
        'auth' => 'none'
    ],

    // Get all users - Private (JWT)
    'GET /users/' => [
        'handler' => function () use ($userController) {
            $userController->getAllRecords();
        },
        'auth' => 'private'
    ],

    // Get a user by ID - Private (JWT)
    'GET /users/{id}/' => [
        'handler' => function ($id) use ($userController) {
            $userController->getRecordById($id);
        },
        'auth' => 'private'
    ],

    // Get a user by Student Number - Private (JWT)
    'GET /users/full/student_number/' => [
        'handler' => function () use ($userController) {
            if (isset($_GET['student_number'])) {
                $userController->getRecordByStudentNumber($_GET['student_number']);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'student_number parameter is required']);
            }
        },
        'auth' => 'private'
    ],

    // Create a new user (Signup) - No Auth
    'POST /users/' => [
        'handler' => function () use ($userController) {
            $userController->createRecord();
        },
        'auth' => 'none'
    ],

    // Update user - Private (JWT)
    'PUT /users/{id}/' => [
        'handler' => function ($id) use ($userController) {
            $userController->updateRecord($id);
        },
        'auth' => 'private'
    ],

    // Delete user (soft delete) - Private (JWT)
    'DELETE /users/{id}/' => [
        'handler' => function ($id) use ($userController) {
            $userController->deleteRecord($id);
        },
        'auth' => 'private'
    ],

];