<?php

require_once __DIR__ . '/../controllers/UserController.php';

$pdo = $GLOBALS['pdo'];
$userController = new UserController($pdo);

return [
    // User Login
    'POST /users/login' => [
        'handler' => [$userController, 'login'],
        'auth' => 'public'
    ],
    // Get all users, with an option to filter by status
    'GET /users/' => [
        'handler' => function () use ($userController) {
            if (isset($_GET['status'])) {
                $userController->getUsersByStatus($_GET['status']);
            } else {
                $userController->getAllRecords();
            }
        },
        'auth' => 'private'
    ],
    // Get the count of users with the status 'student'
    'GET /users/count/student' => [
        'handler' => [$userController, 'getStudentCount'],
        'auth' => 'private'
    ],
    // Get a single user by ID
    'GET /users/{id}' => [
        'handler' => function ($params) use ($userController) {
            $userController->getRecordById($params['id']);
        },
        'auth' => 'private'
    ],
    // Get a single user by Student Number
    'GET /users/student/{studentNumber}' => [
        'handler' => function ($params) use ($userController) {
            $userController->getRecordByStudentNumber($params['studentNumber']);
        },
        'auth' => 'private'
    ],
    // Create a new user
    'POST /users/' => [
        'handler' => [$userController, 'createRecord'],
        'auth' => 'public' // Or 'private' if only admins can create users
    ],
    // Update an existing user
    'PUT /users/{id}' => [
        'handler' => function ($params) use ($userController) {
            $userController->updateRecord($params['id']);
        },
        'auth' => 'private'
    ],
    // Delete a user (soft delete)
    'DELETE /users/{id}' => [
        'handler' => function ($params) use ($userController) {
            $userController->deleteRecord($params['id']);
        },
        'auth' => 'private'
    ],
];
