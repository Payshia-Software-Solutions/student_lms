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

    public function getOrdersByStudent($student_id)
    {
        $stmt = $this->studentOrder->readByStudent($student_id);
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

    public function updateOrderStatus($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $status = $data['order_status'];
        if ($this->studentOrder->updateStatus($id, $status)) {
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
