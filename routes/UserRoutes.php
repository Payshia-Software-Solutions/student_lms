<?php

require_once './controllers/UserController.php';

$pdo = $GLOBALS['pdo'];
$userController = new UserController($pdo);

return [
    // Get all users
    'GET /users/' => function () use ($userController) {
        $userController->getAllRecords();
    },

    // Get a user by ID
    'GET /users/{id}/' => function ($id) use ($userController) {
        $userController->getRecordById($id);
    },

    // Create a new user
    'POST /users/' => function () use ($userController) {
        $userController->createRecord();
    },

    // Update user
    'PUT /users/{id}/' => function ($id) use ($userController) {
        $userController->updateRecord($id);
    },

    // Delete user (soft delete)
    'DELETE /users/{id}/' => function ($id) use ($userController) {
        $userController->deleteRecord($id);
    }
];
