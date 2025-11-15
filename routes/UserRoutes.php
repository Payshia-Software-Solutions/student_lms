<?php

require_once './controllers/UserController.php';

$pdo = $GLOBALS['pdo'];
$userController = new UserController($pdo);

return [
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

    // Login user - No Auth
    'POST /login/' => [
        'handler' => function () use ($userController) {
            $userController->login();
        },
        'auth' => 'none'
    ]
];
