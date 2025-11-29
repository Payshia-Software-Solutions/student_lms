<?php

require_once __DIR__ . '/BaseController.php';

class EnrollmentController extends BaseController
{
    private $enrollment;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->enrollment = new Enrollment($this->db);
    }

    public function handleRequest($method, $endpoint, $id)
    {
        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->getRecordById($this->enrollment, $id);
                } else {
                    return $this->getAllRecords($this->enrollment);
                }
                break;
            case 'POST':
                return $this->createRecord($this->enrollment);
                break;
            case 'PUT':
                 if ($endpoint === 'enrollments' && $id) {
                    return $this->updateEnrollmentStatus($id);
                } else {
                    return $this->errorResponse("Invalid endpoint for PUT request");
                }
                break;
            case 'DELETE':
                return $this->deleteRecord($this->enrollment, $id);
                break;
            default:
                return $this->errorResponse("Method not allowed");
                break;
        }
    }
    
    private function updateEnrollmentStatus($id)
    {
        $data = json_decode(.file_get_contents("php://input"), true);

        if (!isset($data['status'])) {
            return $this->errorResponse("Missing 'status' in request body");
        }

        $status = $data['status'];

        // Validate the status value
        $allowed_statuses = ['pending', 'rejected', 'approved'];
        if (!in_array($status, $allowed_statuses)) {
            return $this->errorResponse("Invalid status value. Must be one of: pending, rejected, approved.");
        }

        if ($this->enrollment->updateStatus($id, $status)) {
            return $this->successResponse(["message" => "Enrollment status updated successfully."]);
        } else {
            return $this->errorResponse("Failed to update enrollment status.");
        }
    }
}
