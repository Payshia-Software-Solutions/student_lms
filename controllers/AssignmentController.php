<?php

require_once __DIR__ . '/../models/Assignment.php';

class AssignmentController
{
    private $assignment;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->assignment = new Assignment($pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $assignments = $this->assignment->getAll();
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }

    public function getRecordById($id)
    {
        $assignment = $this->assignment->getById($id);
        if ($assignment) {
            echo json_encode(['status' => 'success', 'data' => $assignment]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode($_POST['data'], true);

        // Check if a file is uploaded
        if (isset($_FILES['file'])) {
            $file_url = $this->uploadFileViaFTP($_FILES['file']);
            if ($file_url) {
                $data['file_url'] = $file_url;
            } else {
                // Error response is handled in uploadFileViaFTP
                return;
            }
        }

        $newId = $this->assignment->create($data);
        if ($newId) {
            $newAssignment = $this->assignment->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Assignment created successfully', 'data' => $newAssignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->assignment->update($id, $data)) {
            $updatedAssignment = $this->assignment->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Assignment updated successfully', 'data' => $updatedAssignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update assignment']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->assignment->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment']);
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

        $upload_directory_name = 'assignment_files';
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
}
