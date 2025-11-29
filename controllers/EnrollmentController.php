
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
        $result = $this->enrollment->read();
        $this->successResponse($result);
    }

    private function getRecordById($id)
    {
        $this->enrollment->id = $id;
        if ($this->enrollment->read_single()) {
            $record = [
                'id' => $this->enrollment->id,
                'student_id' => $this->enrollment->student_id,
                'course_id' => $this->enrollment->course_id,
                'enrollment_date' => $this->enrollment->enrollment_date,
                'status' => $this->enrollment->status
            ];
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

        $this->enrollment->student_id = $data->student_id;
        $this->enrollment->course_id = $data->course_id;
        // Optional fields
        $this->enrollment->status = $data->status ?? 'pending';

        if ($this->enrollment->create()) {
            $this->successResponse(["message" => "Enrollment created successfully."]);
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
            $this->successResponse(["message" => "Enrollment status updated successfully."]);
        } else {
            $this->errorResponse("Failed to update enrollment status.");
        }
    }

    private function successResponse($data)
    {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($data);
    }

    private function errorResponse($message, $statusCode = 400)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['message' => $message]);
    }
}
