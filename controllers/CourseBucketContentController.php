<?php

require_once __DIR__ . '/../models/CourseBucketContent.php';

class CourseBucketContentController
{
    private $courseBucketContent;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->courseBucketContent = new CourseBucketContent($pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $stmt = $this->courseBucketContent->getAll();
        $courseBucketContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $courseBucketContents]);
    }

    public function getRecordById($id)
    {
        if ($this->courseBucketContent->getById($id)) {
            $courseBucketContent_item = [
                'id' => $this->courseBucketContent->id,
                'course_id' => $this->courseBucketContent->course_id,
                'course_bucket_id' => $this->courseBucketContent->course_bucket_id,
                'content_type' => $this->courseBucketContent->content_type,
                'content_title' => $this->courseBucketContent->content_title,
                'content' => $this->courseBucketContent->content,
                'view_count' => $this->courseBucketContent->view_count,
                'is_active' => $this->courseBucketContent->is_active,
                'created_at' => $this->courseBucketContent->created_at,
                'created_by' => $this->courseBucketContent->created_by,
                'updated_at' => $this->courseBucketContent->updated_at,
                'updated_by' => $this->courseBucketContent->updated_by,
            ];
            echo json_encode(['status' => 'success', 'data' => $courseBucketContent_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course bucket content not found']);
        }
    }
    
    public function getRecordsByCourseBucketId($course_bucket_id)
    {
        $stmt = $this->courseBucketContent->getByCourseBucketId($course_bucket_id);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $contents]);
    }

    public function createRecord()
    {
        $data = json_decode($_POST['data'], true);

        // Check if a file is uploaded
        if (isset($_FILES['file'])) {
            // If content type requires a file upload, handle it
            $file_url = $this->uploadFileViaFTP($_FILES['file']);
            if ($file_url) {
                $data['content'] = $file_url;
            } else {
                // Error response is handled in uploadFileViaFTP
                return;
            }
        }

        $newId = $this->courseBucketContent->create($data);
        if ($newId) {
            if ($this->courseBucketContent->getById($newId)) {
                $courseBucketContent_item = [
                    'id' => $this->courseBucketContent->id,
                    'course_id' => $this->courseBucketContent->course_id,
                    'course_bucket_id' => $this->courseBucketContent->course_bucket_id,
                    'content_type' => $this->courseBucketContent->content_type,
                    'content_title' => $this->courseBucketContent->content_title,
                    'content' => $this->courseBucketContent->content,
                    'view_count' => $this->courseBucketContent->view_count,
                    'is_active' => $this->courseBucketContent->is_active,
                    'created_at' => $this->courseBucketContent->created_at,
                    'created_by' => $this->courseBucketContent->created_by,
                    'updated_at' => $this->courseBucketContent->updated_at,
                    'updated_by' => $this->courseBucketContent->updated_by,
                ];
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Course bucket content created successfully', 'data' => $courseBucketContent_item]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve created course bucket content.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create course bucket content']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->courseBucketContent->update($id, $data)) {
            if ($this->courseBucketContent->getById($id)) {
                $courseBucketContent_item = [
                    'id' => $this->courseBucketContent->id,
                    'course_id' => $this->courseBucketContent->course_id,
                    'course_bucket_id' => $this->courseBucketContent->course_bucket_id,
                    'content_type' => $this->courseBucketContent->content_type,
                    'content_title' => $this->courseBucketContent->content_title,
                    'content' => $this->courseBucketContent->content,
                    'view_count' => $this->courseBucketContent->view_count,
                    'is_active' => $this->courseBucketContent->is_active,
                    'created_at' => $this->courseBucketContent->created_at,
                    'created_by' => $this->courseBucketContent->created_by,
                    'updated_at' => $this->courseBucketContent->updated_at,
                    'updated_by' => $this->courseBucketContent->updated_by,
                ];
                echo json_encode(['status' => 'success', 'message' => 'Course bucket content updated successfully', 'data' => $courseBucketContent_item]);
            } else {
                 http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Course bucket content not found after update']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update course bucket content']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->courseBucketContent->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Course bucket content deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete course bucket content']);
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

        $upload_directory_name = 'course_content_files'; // <-- Changed directory name
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
