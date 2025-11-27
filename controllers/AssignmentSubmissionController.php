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

    // ... other functions ...

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
            // Previous file is NOT deleted to keep submission history
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
        // ... (validation) ...
        
        // Previous file is NOT deleted to keep submission history
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

    // ... (other functions) ...

    private function uploadFileViaFTP($file)
    {
        // ... (connection logic) ...

        // Sanitize the filename to prevent issues with spaces and special characters
        $original_filename = basename($file['name']);
        $sanitized_filename = preg_replace('/[^A-Za-z0-9\._-]', '', str_replace(' ', '_', $original_filename));
        $file_name = uniqid() . '-' . $sanitized_filename;
        $remote_path = $remote_dir . '/' . $file_name;

        // ... (upload logic) ...
        
        return $public_url_base . '/' . $upload_directory_name . '/' . $file_name;
    }

    private function deleteFileViaFTP($file_url)
    {
        // This function is no longer called on re-submission but is kept for other potential uses.
        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');

        $relative_path = str_replace($public_url_base, '', $file_url);
        $decoded_path = urldecode($relative_path);
        $remote_path = $ftp_root . $decoded_path;

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            error_log('FTP connection failed for file deletion.');
            return false;
        }

        if (!@ftp_delete($conn_id, $remote_path)) {
            error_log("Could not delete FTP file: {$remote_path}");
        }

        ftp_close($conn_id);
        return true;
    }
}
