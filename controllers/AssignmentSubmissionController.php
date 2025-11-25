<?php

require_once __DIR__ . '/../models/AssignmentSubmission.php';

class AssignmentSubmissionController
{
    private $assignmentSubmission;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->assignmentSubmission = new AssignmentSubmission($pdo);
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

    public function createRecord()
    {
        // --- FTP and File Handling ---

        // **FIX: Add validation for the FTP configuration**
        if (!is_array($this->ftp_config) || empty($this->ftp_config['server'])) {
            http_response_code(500);
            // Log the faulty config for debugging purposes
            error_log('AssignmentSubmissionController: Invalid or empty FTP configuration loaded. Value: ' . print_r($this->ftp_config, true));
            echo json_encode(['status' => 'error', 'message' => 'Server-side FTP configuration error. Please contact an administrator.']);
            return;
        }

        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');

        if (!isset($_FILES['assignment_file'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No assignment file was uploaded.']);
            return;
        }

        $file = $_FILES['assignment_file'];
        $tmp_path = $file['tmp_name'];

        // FTP connection
        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP connection failed.']);
            return;
        }

        if (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP login failed.']);
            ftp_close($conn_id);
            return;
        }
        
        ftp_pasv($conn_id, true);

        $upload_directory_name = 'assignment_submissions';
        $remote_dir = $ftp_root . '/' . $upload_directory_name;

        // Check if directory exists, if not create it
        if (!@ftp_chdir($conn_id, $remote_dir)) {
            if (!ftp_mkdir($conn_id, $remote_dir)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create directory on FTP server. Check permissions.']);
                ftp_close($conn_id);
                return;
            }
        }

        $file_name = uniqid() . '-' . basename($file['name']);
        $remote_path = $remote_dir . '/' . $file_name;

        if (!ftp_put($conn_id, $remote_path, $tmp_path, FTP_BINARY)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP file upload failed.']);
            ftp_close($conn_id);
            return;
        }

        ftp_close($conn_id);

        // --- End of FTP Handling ---

        $data = json_decode($_POST['data'], true);
        
        // Construct the public URL using the base URL and the upload directory
        $data['file_path'] = $public_url_base . '/' . $upload_directory_name . '/' . $file_name;

        $newId = $this->assignmentSubmission->create($data);

        if ($newId) {
            $submission = $this->assignmentSubmission->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Assignment submission created successfully', 'data' => $submission]);
        } else {
            // Optional: You might want to delete the uploaded file if DB insertion fails
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment submission record in database.']);
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

    public function deleteRecord($id)
    {
        if ($this->assignmentSubmission->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment submission deleted successfully (soft delete)']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment submission']);
        }
    }
}
