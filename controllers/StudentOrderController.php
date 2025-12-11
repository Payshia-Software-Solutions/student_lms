<?php

require_once __DIR__ . '/../models/StudentOrder.php';

class StudentOrderController
{
    private $studentOrder;

    public function __construct($pdo)
    {
        $this->studentOrder = new StudentOrder($pdo);
    }

    public function getLatestOrderByStudent()
    {
        if (!isset($_GET['student_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'student_number is required.']);
            return;
        }

        $student_number = $_GET['student_number'];
        $latest_order = $this->studentOrder->getLatestByStudentNumber($student_number);

        if ($latest_order) {
            echo json_encode(['status' => 'success', 'data' => $latest_order]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No orders found for this student.']);
        }
    }

    public function getAllRecords()
    {
        $stmt = $this->studentOrder->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordsByFilter()
    {
        $filters = [];
        if(isset($_GET['course_id'])) $filters['course_id'] = $_GET['course_id'];
        if(isset($_GET['course_bucket_id'])) $filters['course_bucket_id'] = $_GET['course_bucket_id'];
        if(isset($_GET['order_status'])) $filters['status'] = $_GET['order_status'];
        if(isset($_GET['student_number'])) $filters['student_number'] = $_GET['student_number'];

        $stmt = $this->studentOrder->getFiltered($filters);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        $record = $this->studentOrder->read_single($id);
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->studentOrder->create($data);
        if ($newId) {
            $record = $this->studentOrder->read_single($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Record created successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create record']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->studentOrder->update($id, $data)) {
            $record = $this->studentOrder->read_single($id);
            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update record']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentOrder->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete record']);
        }
    }
}
