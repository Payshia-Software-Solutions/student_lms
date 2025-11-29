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
                $this->updateRecord($id);
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

    private function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty(get_object_vars($data))) {
            $this->errorResponse("No data provided for update.");
            return;
        }

        if ($this->enrollment->update($id, $data)) {
            $record = $this->getEnrollmentData($id);
            $this->successResponse([
                'message' => 'Enrollment updated successfully.',
                'data' => $record
            ]);
        } else {
            $this->errorResponse("Failed to update enrollment.");
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
