<?php

require_once __DIR__ . '/../models/OrderableItem.php';

class OrderableItemController
{
    private $pdo;
    private $orderableItem;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->pdo = $pdo;
        $this->orderableItem = new OrderableItem($this->pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $stmt = $this->orderableItem->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($records);
    }

    public function getRecordById($id)
    {
        $record = $this->orderableItem->read_single($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse('Record not found.', 404);
        }
    }
    
    public function getRecordsByCourse()
    {
        if (isset($_GET['course_id']) && isset($_GET['course_bucket_id'])) {
            $course_id = $_GET['course_id'];
            $course_bucket_id = $_GET['course_bucket_id'];
            
            $stmt = $this->orderableItem->readByCourse($course_id, $course_bucket_id);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->successResponse($records);
        } else {
            $this->errorResponse('Missing required parameters: course_id and course_bucket_id.', 400);
        }
    }

    public function createRecord()
    {
        $data = $_POST;
        if (isset($_FILES['img_url']) && $_FILES['img_url']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['img_url'];
            $fileName = basename($file['name']);

            // Define paths relative to the FTP user's root directory
            $project_dir = 'qa-lms-server.payshia.com';
            $upload_folder = 'orderable_item';
            $remote_file_path = $project_dir . '/' . $upload_folder . '/' . $fileName;
            
            $ftp_conn = ftp_connect($this->ftp_config['server']);
            if (!$ftp_conn) {
                $this->errorResponse('FTP connection failed.', 500);
                return;
            }

            if (ftp_login($ftp_conn, $this->ftp_config['username'], $this->ftp_config['password'])) {
                ftp_pasv($ftp_conn, true);

                // Navigate into the project directory and create the upload folder
                if (@ftp_chdir($ftp_conn, $project_dir)) {
                    @ftp_mkdir($ftp_conn, $upload_folder);
                }
                
                // Upload the file to the correct path
                if (ftp_put($ftp_conn, $upload_folder . '/' . $fileName, $file['tmp_name'], FTP_BINARY)) {
                    $public_url = 'https://' . $project_dir . '/' . $upload_folder . '/' . $fileName;
                    $data['img_url'] = $public_url;
                } else {
                    $this->errorResponse('FTP upload failed. Please check path and permissions.', 500);
                    ftp_close($ftp_conn);
                    return;
                }
            } else {
                $this->errorResponse('FTP login failed.', 500);
                ftp_close($ftp_conn);
                return;
            }
            ftp_close($ftp_conn);
        }

        $id = $this->orderableItem->create($data);
        if ($id) {
            $this->successResponse(['id' => $id, 'message' => 'Record created successfully.'], 201);
        } else {
            $this->errorResponse('Failed to create record.', 500);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->orderableItem->update($id, $data)) {
            $this->successResponse(['id' => $id, 'message' => 'Record updated successfully.']);
        } else {
            $this->errorResponse('Failed to update record.', 500);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->orderableItem->delete($id)) {
            $this->successResponse(['id' => $id, 'message' => 'Record deleted successfully.']);
        } else {
            $this->errorResponse('Failed to delete record.', 500);
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
