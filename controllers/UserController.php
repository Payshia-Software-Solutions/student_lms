<?php

require_once './models/User.php';

class UserController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);
    }

    // Get all users
    public function getAllRecords()
    {
        echo json_encode($this->userModel->getAll());
    }

    // Get by ID
    public function getRecordById($id)
    {
        echo json_encode($this->userModel->getById($id));
    }

    // Create a user
    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->userModel->create($data);
        echo json_encode(['success' => $result]);
    }

    // Update user
    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->userModel->update($id, $data);
        echo json_encode(['success' => $result]);
    }

    // Delete user
    public function deleteRecord($id)
    {
        $result = $this->userModel->delete($id);
        echo json_encode(['success' => $result]);
    }
}
