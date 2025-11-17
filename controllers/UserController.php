<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JwtHelper.php';

class UserController
{
    private $user;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
    }

    public function getAllRecords()
    {
        $users = $this->user->getAll();
        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    public function getRecordById($id)
    {
        $user = $this->user->getById($id);
        if ($user) {
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->user->create($data)) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create user']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->user->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update user']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->user->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully (soft delete)']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete user']);
        }
    }

  // Add this method to your UserController.php class

public function login()
{
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        return;
    }
    
    $user = $this->user->login($data['email'], $data['password']);
    
    if ($user) {
        // Generate JWT token
        $token = JwtHelper::generateToken([
            'id' => $user['id'],
            'email' => $user['email'],
            'f_name' => $user['f_name'],
            'l_name' => $user['l_name']
        ]);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
}
}
