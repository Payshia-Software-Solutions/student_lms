<?php

class EnrollmentController
{
    private $db;
    private $enrollment;

    public function __construct($db)
    {
        $this->db = $db;
        $this->enrollment = new Enrollment($this->db);
    }

    public function handleRequest($method, $id)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getRecordById($id);
                } else {
                    $this->getAllRecords();
                }
                break;
            case 'POST':
                $this->createRecord();
                break;
            case 'PUT':
                $this->updateEnrollmentStatus($id);
                break;
            case 'DELETE':
                $this->deleteRecord($id);
                break;
            default:
                $this->errorResponse("Method not allowed");
                break;
        }
    }

    private function getAllRecords()
    {
        $stmt = $this->enrollment->read();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($enrollments);
    }

    private function getEnrollmentData($id)
    {
        $this->enrollment->id = $id;
        if ($this->enrollment->read_single()) {
            return [
                'id' => (int)$this->enrollment->id,
                'student_id' => (int)$this->enrollment->student_id,
                'course_id' => (int)$this->enrollment->course_id,
                'enrollment_date' => $this->enrollment->enrollment_date,
                'grade' => $this->enrollment->grade,
                'status' => $this->enrollment->status
            ];
        }
        return null;
    }

    private function getRecordById($id)
    {
        $record = $this->getEnrollmentData($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse("Enrollment not found.");
        }
    }

    private function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->student_id) || !isset($data->course_id)) {
            $this->errorResponse("Missing student_id or course_id in request body");
            return;
        }

        $new_id = $this->enrollment->create($data);

        if ($new_id) {
            $record = $this->getEnrollmentData($new_id);
            $this->successResponse([
                'message' => 'Enrollment created successfully.',
                'data' => $record
            ], 201);
        } else {
            $this->errorResponse("Failed to create enrollment.");
        }
    }

    private function deleteRecord($id)
    {
        $this->enrollment->id = $id;
        if ($this->enrollment->delete()) {
            $this->successResponse(["message" => "Enrollment deleted successfully."]);
        } else {
            $this->errorResponse("Failed to delete enrollment.");
        }
    }

    private function updateEnrollmentStatus($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['status'])) {
            $this->errorResponse("Missing 'status' in request body");
            return;
        }

        $status = $data['status'];
        $allowed_statuses = ['pending', 'rejected', 'approved'];
        if (!in_array($status, $allowed_statuses)) {
            $this->errorResponse("Invalid status value. Must be one of: pending, rejected, approved.");
            return;
        }

        if ($this->enrollment->updateStatus($id, $status)) {
            $record = $this->getEnrollmentData($id);
            $this->successResponse([
                'message' => 'Enrollment status updated successfully.',
                'data' => $record
            ]);
        } else {
            $this->errorResponse("Failed to update enrollment status.");
        }
    }

    private function successResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(${
  "message": "Enrollment created successfully.",
  "data": {
    "id": 2,
    "student_id": 1,
    "course_id": 1,
    "enrollment_date": "2024-07-31",
    "grade": null,
    "status": "pending"
  }
}
);
    }

    private function errorResponse($message, $statusCode = 400)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['message' => $message]);
    }
}
