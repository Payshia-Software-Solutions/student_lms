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
        if (!isset($_GET['course_id']) || !isset($_GET['course_bucket_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'course_id and course_bucket_id are required.']);
            return;
        }

        $filters = [
            'course_id' => filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT),
            'course_bucket_id' => filter_input(INPUT_GET, 'course_bucket_id', FILTER_SANITIZE_NUMBER_INT),
            'student_number' => filter_input(INPUT_GET, 'student_number', FILTER_SANITIZE_STRING),
            'assigment_id' => filter_input(INPUT_GET, 'assigment_id', FILTER_SANITIZE_NUMBER_INT),
            'sub_status' => filter_input(INPUT_GET, 'sub_status', FILTER_SANITIZE_STRING)
        ];

        $filters = array_filter($filters);

        $submissions = $this->assignmentSubmission->getByFilters($filters);
        echo json_encode(['status' => 'success', 'data' => $submissions]);
    }

    public function createRecord()
    {
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

        $assignment = $this->assignment->getById($assigment_id);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Associated assignment not found.']);
            return;
        }
        $submission_limit = $assignment['submition_count'];

        $existingSubmissions = $this->assignmentSubmission->getByFilters([
            'student_number' => $student_number,
            'assigment_id' => $assigment_id
        ]);
        
        $current_submission_count = count($existingSubmissions);
        $is_resubmitting_rejected = false;

        if ($current_submission_count > 0) {
            $latest_submission = end($existingSubmissions);
            if ($latest_submission && isset($latest_submission['sub_status']) && $latest_submission['sub_status'] === 'rejected') {
                $is_resubmitting_rejected = true;
            }
        }

        if (!$is_resubmitting_rejected) {
            if ($submission_limit > 0 && $current_submission_count >= $submission_limit) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Submission limit reached. You cannot submit this assignment again.']);
                return;
            }
            if ($submission_limit <= 0) {
                 http_response_code(403);
                 echo json_encode(['status' => 'error', 'message' => 'This assignment does not accept submissions.']);
                 return;
            }
        }

        $file_url = $this->uploadFileViaFTP($_FILES['assignment_file']);
        if (!$file_url) return;

        $data['file_path'] = $file_url;
        $data['sub_count'] = $current_submission_count + 1;
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
        $submission = $this->assignmentSubmission->getById($id);
        if ($submission && !empty($submission['file_path'])) {
            $this->deleteFileViaFTP($submission['file_path']);
        }

        if ($this->assignmentSubmission->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment submission deleted permanently']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment submission record.']);
        }
    }

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

        if (!@is_dir("ftp://$ftp_user:$ftp_pass@$ftp_server/$remote_dir")) {
            if (!@ftp_mkdir($conn_id, $remote_dir)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => "Failed to create directory on FTP server: $remote_dir"]);
                ftp_close($conn_id);
                return false;
            }
        }

        $original_filename = basename($file['name']);
        $sanitized_filename = preg_replace('/[^A-Za-z0-9\._-]/', '_', str_replace(' ', '_', $original_filename));
        $file_name = uniqid() . '-' . $sanitized_filename;
        $remote_path = $remote_dir . '/' . $file_name;

        if (!ftp_put($conn_id, $remote_path, $tmp_path, FTP_BINARY)) {
            http_response_code(500);
            error_log("FTP upload failed. Tried to upload to: " . $remote_path); 
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
        
        $relative_path = str_replace($public_url_base, '', $file_url);
        $remote_file_path = $ftp_root . urldecode($relative_path);

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            error_log('FTP connection failed for file deletion.');
            return false;
        }

        if (!@ftp_delete($conn_id, $remote_file_path)) {
            error_log("Could not delete FTP file: {$remote_file_path}");
        }

        ftp_close($conn_id);
        return true;
    }
}