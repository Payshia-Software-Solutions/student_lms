<?php

require_once __DIR__ . '/../models/Assignment.php';

class AssignmentController
{
    private $assignment;

    public function __construct($pdo)
    {
        $this->assignment = new Assignment($pdo);
    }

    public function getAllRecords()
    {
        $assignments = $this->assignment->getAll();
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }

    public function getRecordById($id)
    {
        $assignment = $this->assignment->getById($id);
        if ($assignment) {
            echo json_encode(['status' => 'success', 'data' => $assignment]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->assignment->create($data);

        if ($newId) {
            $assignment = $this->assignment->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Assignment created successfully', 'data' => $assignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->assignment->update($id, $data)) {
            $assignment = $this->assignment->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Assignment updated successfully', 'data' => $assignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update assignment']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->assignment->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment']);
        }
    }
}
