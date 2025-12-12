<?php

require_once __DIR__ . '/../models/CourseBucketContent.php';
require_once __DIR__ . '/../models/Assignment.php';

class CourseBucketContentController
{
    private $courseBucketContent;
    private $assignment;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->courseBucketContent = new CourseBucketContent($pdo);
        $this->assignment = new Assignment($pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        if (isset($_GET['course_id']) && isset($_GET['course_bucket_id'])) {
            $courseBucketContents = $this->courseBucketContent->getByCourseAndBucket($_GET['course_id'], $_GET['course_bucket_id']);
        } else {
            $courseBucketContents = $this->courseBucketContent->getAll();
        }
        echo json_encode(['status' => 'success', 'data' => $courseBucketContents]);
    }

    public function getRecordById($id)
    {
        if ($this->courseBucketContent->getById($id)) {
            $courseBucketContent_item = $this->buildContentItemResponse($this->courseBucketContent);
            
            // Fetch related assignments
            $assignments = $this->assignment->getByCourseAndBucket($this->courseBucketContent->course_id, $this->courseBucketContent->course_bucket_id);
            $courseBucketContent_item['assignments'] = $assignments;

            echo json_encode(['status' => 'success', 'data' => $courseBucketContent_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course bucket content not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode($_POST['data'], true);

        // Check if a file is uploaded
        if (isset($_FILES['file'])) {
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
            $record = $this->courseBucketContent->getById($newId);
            if ($record) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Course bucket content created successfully', 'data' => $record]);
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
        if (!isset($_POST['data'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Update data not provided in the \'data\' field.']);
            return;
        }

        $data = json_decode($_POST['data'], true);

        // Handle file upload if a new file is provided for the update.
        if (isset($_FILES['file'])) {
            $file_url = $this->uploadFileViaFTP($_FILES['file']);
            if ($file_url) {
                $data['content'] = $file_url;
            } else {
                // Error response is handled within the FTP function.
                return;
            }
        }
    
        if ($this->courseBucketContent->update($id, $data)) {
            $updatedRecord = $this->courseBucketContent->getById($id);
            if ($updatedRecord) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Course bucket content updated successfully', 
                    'data' => $updatedRecord
                ]);
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
    
    // --- PRIVATE HELPER METHODS ---

    private function buildContentItemResponse($content)
    {
        return [
            'id' => $content->id,
            'course_id' => $content->course_id,
            'course_bucket_id' => $content->course_bucket_id,
            'content_type' => $content->content_type,
            'content_title' => $content->content_title,
            'content' => $content->content,
            'view_count' => $content->view_count,
            'is_active' => $content->is_active,
            'created_at' => $content->created_at,
            'created_by' => $content->created_by,
            'updated_at' => $content->updated_at,
            'updated_by' => $content->updated_by,
        ];
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

        $conn_.id = ftp_connect($ftp_server);
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
