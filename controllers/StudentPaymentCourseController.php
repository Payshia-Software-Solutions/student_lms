<?php

require_once __DIR__ . '/../models/StudentPaymentCourse.php';

class StudentPaymentCourseController
{
    private $studentPaymentCourse;

    public function __construct($pdo)
    {
        $this->studentPaymentCourse = new StudentPaymentCourse($pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->studentPaymentCourse->getAll();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        if ($this->studentPaymentCourse->getById($id)) {
            $record_item = [
                'id' => $this->studentPaymentCourse->id,
                'course_id' => $this->studentPaymentCourse->course_id,
                'course_bucket_id' => $this->studentPaymentCourse->course_bucket_id,
                'student_number' => $this->studentPaymentCourse->student_number,
                'payment_request_id' => $this->studentPaymentCourse->payment_request_id,
                'payment_amount' => $this->studentPaymentCourse->payment_amount,
                'discount_amount' => $this->studentPaymentCourse->discount_amount,
                'created_at' => $this->studentPaymentCourse->created_at,
            ];
            echo json_encode(['status' => 'success', 'data' => $record_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->studentPaymentCourse->create($data);
        if ($newId) {
            if ($this->studentPaymentCourse->getById($newId)) {
                $record_item = [
                    'id' => $this->studentPaymentCourse->id,
                    'course_id' => $this->studentPaymentCourse->course_id,
                    'course_bucket_id' => $this->studentPaymentCourse->course_bucket_id,
                    'student_number' => $this->studentPaymentCourse->student_number,
                    'payment_request_id' => $this->studentPaymentCourse->payment_request_id,
                    'payment_amount' => $this->studentPaymentCourse->payment_amount,
                    'discount_amount' => $this->studentPaymentCourse->discount_amount,
                    'created_at' => $this->studentPaymentCourse->created_at,
                ];
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Record created successfully', 'data' => $record_item]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve created record.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create record']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->studentPaymentCourse->update($id, $data)) {
            if ($this->studentPaymentCourse->getById($id)) {
                $record_item = [
                    'id' => $this->studentPaymentCourse->id,
                    'course_id' => $this->studentPaymentCourse->course_id,
                    'course_bucket_id' => $this->studentPaymentCourse->course_bucket_id,
                    'student_number' => $this->studentPaymentCourse->student_number,
                    'payment_request_id' => $this->studentPaymentCourse->payment_request_id,
                    'payment_amount' => $this->studentPaymentCourse->payment_amount,
                    'discount_amount' => $this->studentPaymentCourse->discount_amount,
                    'created_at' => $this->studentPaymentCourse->created_at,
                ];
                echo json_encode(['status' => 'success', 'message' => 'Record updated successfully', 'data' => $record_item]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Record not found after update']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update record']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentPaymentCourse->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete record']);
        }
    }
}
