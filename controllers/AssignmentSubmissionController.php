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
        // --- Configuration & Input Validation ---
        if (!is_array($this->ftp_config) || empty($this->ftp_config['server'])) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server-side FTP configuration error.']);
            return;
        }

        if (!isset($_FILES['assignment_file'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No assignment file was uploaded.']);
            return;
        }

        $data = json_decode($_POST['data'], true);
        $student_number = $data['student_number'] ?? null;
        $assigment_id = $data['assigment_id'] ?? null;
        
        if (!$student_number || !$assigment_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Student number and assignment ID are required.']);
            return;
        }

        // 1. Get Assignment Data for Submission Limit
        $assignment = $this->assignment->getById($assigment_id);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Associated assignment not found.']);
            return;
        }
        $submission_limit = $assignment['submition_count'];

        // 2. Check for Existing Submissions using getByFilters
        $existingSubmissions = $this->assignmentSubmission->getByFilters([
            'student_number' => $student_number,
            'assigment_id' => $assigment_id
        ]);
        
        // 3. Handle Re-submission or New Submission
        if (!empty($existingSubmissions)) {
            // --- This is a RE-SUBMISSION ---
            $existingSubmission = $existingSubmissions[0]; // Assuming one submission record per student/assignment

            // 4. Enforce Submission Limit
            if ($existingSubmission['sub_count'] >= $submission_limit) {
                http_response_code(403); // Forbidden
                echo json_encode(['status' => 'error', 'message' => 'Submission limit reached. You cannot submit this assignment again.']);
                return;
            }

            // --- Re-submission Logic (Update Existing) ---
            $this->deleteFileViaFTP($existingSubmission['file_path']); 
            $new_file_url = $this->uploadFileViaFTP($_FILES['assignment_file']); 
            if (!$new_file_url) return;

            $updateData = [
                'file_path' => $new_file_url,
                'sub_count' => $existingSubmission['sub_count'] + 1,
                'sub_status' => 'submitted'
            ];

            if ($this->assignmentSubmission->patch($existingSubmission['id'], $updateData)) {
                $updatedSubmission = $this->assignmentSubmission->getById($existingSubmission['id']);
                echo json_encode(['status' => 'success', 'message' => 'Assignment re-submitted successfully', 'data' => $updatedSubmission]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update submission record.']);
            }

        } else {
            // --- This is a NEW SUBMISSION ---
            if (0 >= $submission_limit) {
                 http_response_code(403);
                 echo json_encode(['status' => 'error', 'message' => 'This assignment does not accept submissions.']);
                 return;
            }
            
            $file_url = $this->uploadFileViaFTP($_FILES['assignment_file']);
            if (!$file_url) return;

            $data['file_path'] = $file_url;
            $data['sub_count'] = 1; // First submission
            $data['sub_status'] = 'submitted';

            $newId = $this->assignmentSubmission->create($data);

            if ($newId) {
                $submission = $this->assignmentSubmission->getById($newId);
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Assignment submission created successfully', 'data' => $submission]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment submission.']);
            }
        }
    }
    
    public function updateSubmissionFile($id)
    {
        if (!is_array($this->ftp_config) || empty($this->ftp_config['server'])) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server-side FTP configuration error.']);
            return;
        }

        if (!isset($_FILES['assignment_file'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No new assignment file was uploaded.']);
            return;
        }

        $existingSubmission = $this->assignmentSubmission->getById($id);
        if (!$existingSubmission) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found.']);
            return;
        }

        $assignment = $this->assignment->getById($existingSubmission['assigment_id']);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Associated assignment not found.']);
            return;
        }

        if ($existingSubmission['sub_count'] >= $assignment['submition_count']) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Submission limit reached. You cannot update this assignment file.']);
            return;
        }
        
        if (!empty($existingSubmission['file_path'])) {
            $this->deleteFileViaFTP($existingSubmission['file_path']);
        }

        $new_file_url = $this->uploadFileViaFTP($_FILES['assignment_file']);
        if (!$new_file_url) return;

        $updateData = [
            'file_path' => $new_file_url,
            'sub_count' => $existingSubmission['sub_count'] + 1,
            'sub_status' => 'submitted'
        ];
        if ($this->assignmentSubmission->patch($id, $updateData)) {
            $updatedSubmission = $this->assignmentSubmission->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'File updated successfully', 'data' => $updatedSubmission]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update file path in the database.']);
        }
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

    public function updateSubmissionStatus()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);

        if (!$id || !$status) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing id or status parameter.']);
            return;
        }

        $allowed_statuses = ['submitted', 'graded', 'rejected'];
        if (!in_array($status, $allowed_statuses)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid submission status.']);
            return;
        }

        $existingSubmission = $this->assignmentSubmission->getById($id);
        if (!$existingSubmission) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found.']);
            return;
        }

        $updateData = ['sub_status' => $status];
        if ($this->assignmentSubmission->patch($id, $updateData)) {
            $updatedSubmission = $this->assignmentSubmission->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Submission status updated successfully', 'data' => $updatedSubmission]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update submission status in the database.']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->assignmentSubmission->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment submission deleted permanently']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment submission']);
        }
    }

    // --- PRIVATE HELPER METHODS FOR FTP ---

    private function uploadFileViaFTP($file)
    {
        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');
        $tmp_path = $file['tmp_name'];

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP connection failed.']);
            return false;
        }
        
        ftp_pasv($conn_id, true);

        $upload_directory_name = 'assignment_submissions';
        $remote_dir = $ftp_root . '/' . $upload_directory_name;

        if (!@ftp_chdir($conn_id, $remote_dir) && !ftp_mkdir($conn_id, $remote_dir)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create directory on FTP server.']);
            ftp_close($conn_id);
            return false;
        }

        $file_name = uniqid() . '-' . basename($file['name']);
        $remote_path = $remote_dir . '/' . $file_name;

        if (!ftp_put($conn_id, $remote_path, $tmp_path, FTP_BINARY)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP file upload failed.']);
            ftp_close($conn_id);
            return false;
        }

        ftp_close($conn_id);
        return $public_url_base . '/' . $upload_directory_name . '/' . $file_name;
    }

    private function deleteFileViaFTP($file_url)
    {
        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');

        // Derive the server path from the public URL
        $relative_path = str_replace($public_url_base, '', $file_url);
        $remote_path = $ftp_root . $relative_path;

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            error_log('FTP connection failed for file deletion.');
            return false; // Don't send a response to the client
        }

        ftp_delete($conn_id, $remote_path);
        ftp_close($conn_id);
        return true;
    }
}
