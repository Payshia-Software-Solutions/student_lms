<?php

require_once __DIR__ . '/../models/UserFullDetails.php';

class UserFullDetailsController
{
    private $pdo;
    private $userFullDetails;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userFullDetails = new UserFullDetails($this->pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->userFullDetails->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($records);
    }

    public function getRecordById($id)
    {
        $record = $this->userFullDetails->read_single($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse("Record not found.", 404);
        }
    }
    
    public function getRecordByStudentNumber($student_number)
    {
        $record = $this->userFullDetails->read_by_student_number($student_number);
        if ($record) {
            $this->successResponse(['found' => true, 'data' => $record]);
        } else {
            $this->successResponse(['found' => false, 'data' => null]);
        }
    }

    public function getRecordByStudentNumberQuery()
    {
        if (isset($_GET['student_number'])) {
            $this->getRecordByStudentNumber($_GET['student_number']);
        } else {
            $this->errorResponse("Student number is required.", 400);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->userFullDetails->create($data);
        if ($id) {
            $this->successResponse(['id' => $id, 'message' => 'Record created successfully.'], 201);
        } else {
            $this->errorResponse("Failed to create record.", 500);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userFullDetails->update($id, $data)) {
            $this->successResponse(['id' => $id, 'message' => 'Record updated successfully.']);
        } else {
            $this->errorResponse("Failed to update record.", 500);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->userFullDetails->delete($id)) {
            $this->successResponse(['id' => $id, 'message' => 'Record deleted successfully.']);
        } else {
            $this->errorResponse("Failed to delete record.", 500);
        }
    }

    private function successResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function errorResponse($message, $statusCode = 400)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['message' => $message]);
    }
}
