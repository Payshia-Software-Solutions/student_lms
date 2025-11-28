<?php

require_once __DIR__ . '/../models/AssignmentSubmission.php';
require_once __DIR__ . '/../models/Assignment.php'; 

class AssignmentSubmissionController
{
    private $assignmentSubmission;
    private $assignment; 
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->assignmentSubmission = new AssignmentSubmission($pdo);
        $this->assignment = new Assignment($pdo); 
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $submissions = $this->assignmentSubmission->getAll();
        echo json_encode(['status' => 'success', 'data' => $submissions]);
    }

    public function getRecordById($id)
    {
        $submission = $this->assignmentSubmission->getById($id);
        if ($submission) {
            echo json_encode(['status' => 'success', 'data' => $submission]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found']);
        }
    }

    public function getRecordsByFilter()
    {
        $filters = [
            'student_number' => filter_input(INPUT_GET, 'student_number', FILTER_SANITIZE_STRING),
            'course_id' => filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT),
            'course_bucket_id' => filter_input(INPUT_GET, 'course_bucket_id', FILTER_SANITIZE_NUMBER_INT),
            'assigment_id' => filter_input(INPUT_GET, 'assigment_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        $filters = array_filter($filters);

        $submissions = $this->assignmentSubmission->getByFilters($filters);

        if (!empty($submissions)) {
            echo json_encode(['status' => 'success', 'data' => $submissions]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No assignment submissions found matching the specified criteria.']);
        }
    }

    public function createRecord()
    {
        // ... (code is unchanged) ...
    }
    
    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->assignmentSubmission->update($id, $data)) {
            $submission = $this->assignmentSubmission->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Assignment submission updated successfully', 'data' => $submission]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update assignment submission']);
        }
    }

    public function updateGrade()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $grade = filter_input(INPUT_GET, 'grade', FILTER_SANITIZE_STRING);

        if (!$id || $grade === null) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing id or grade parameter.']);
            return;
        }

        $existingSubmission = $this->assignmentSubmission->getById($id);
        if (!$existingSubmission) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found.']);
            return;
        }

        $updateData = [
            'grade' => $grade,
            'sub_status' => 'graded'
        ];

        if ($this->assignmentSubmission->patch($id, $updateData)) {
            $updatedSubmission = $this->assignmentSubmission->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Grade updated successfully', 'data' => $updatedSubmission]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update grade in the database.']);
        }
    }

    public function updateSubmissionStatus()
    {
        // ... (code is unchanged) ...
    }

    public function deleteRecord($id)
    {
        // ... (code is unchanged) ...
    }

    // ... (private FTP functions are unchanged) ...
}
