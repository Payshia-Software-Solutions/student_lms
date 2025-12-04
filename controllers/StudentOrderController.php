<?php

require_once __DIR__ . '/../models/StudentOrder.php';

class StudentOrderController
{
    private $pdo;
    private $studentOrder;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->studentOrder = new StudentOrder($this->pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->studentOrder->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($records);
    }

    public function getRecordById($id)
    {
        $record = $this->studentOrder->read_single($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse("Record not found.", 404);
        }
    }

    public function getFilteredRecords()
    {
        $filters = [
            'course_id' => $_GET['course_id'] ?? null,
            'course_bucket_id' => $_GET['course_bucket_id'] ?? null,
            'status' => $_GET['status'] ?? null
        ];

        $stmt = $this->studentOrder->getFiltered($filters);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($records);
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->studentOrder->create($data);
        if ($id) {
            $this->successResponse(['id' => $id, 'message' => 'Record created successfully.'], 201);
        } else {
            $this->errorResponse("Failed to create record.", 500);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->studentOrder->update($id, $data)) {
            $this->successResponse(['id' => $id, 'message' => 'Record updated successfully.']);
        } else {
            $this->errorResponse("Failed to update record.", 500);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentOrder->delete($id)) {
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
