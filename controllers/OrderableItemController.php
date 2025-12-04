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
            $this->errorResponse("Record not found.", 404);
        }
    }

    public function createRecord()
    {
        $data = $_POST;
        if (isset($_FILES['img_url'])) {
            $file = $_FILES['img_url'];
            $fileName = basename($file['name']);
{
    "student_id": "STU12345",
    "orderable_item_id": 15,
    "order_status": "pending",
    "address_line_1": "No. 42, Main Street",
    "address_line_2": "Apt. 3B",
    "city": "Colombo",
    "district": "Colombo",
    "postal_code": "00500",
    "phone_number_1": "0771234567",
    "phone_number_2": "0112345678"
}
            $upload_dir = "orderable_item"; // Use a relative path
            $remote_file_path = $upload_dir . "/" . $fileName;

            $ftp_conn = ftp_connect($this->ftp_config['server']);
            if (!$ftp_conn) {
                $this->errorResponse("FTP connection failed.", 500);
                return;
            }
            
            if (ftp_login($ftp_conn, $this->ftp_config['username'], $this->ftp_config['password'])) {
                ftp_pasv($ftp_conn, true);
                
                // Try to create the directory in the FTP user's root, suppress errors if it already exists
                @ftp_mkdir($ftp_conn, $upload_dir);

                if (ftp_put($ftp_conn, $remote_file_path, $file['tmp_name'], FTP_BINARY)) {
                    $data['img_url'] = "https://qa-lms-server.payshia.com/orderable_item/" . $fileName;
                } else {
                    $this->errorResponse("Failed to upload file to FTP server.", 500);
                    ftp_close($ftp_conn);
                    return;
                }
            } else {
                $this->errorResponse("FTP login failed.", 500);
                ftp_close($ftp_conn);
                return;
            }
            ftp_close($ftp_conn);
        }

        $id = $this->orderableItem->create($data);
        if ($id) {
            $this->successResponse(['id' => $id, 'message' => 'Record created successfully.'], 201);
        } else {
            $this->errorResponse("Failed to create record.", 500);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->orderableItem->update($id, $data)) {
            $this->successResponse(['id' => $id, 'message' => 'Record updated successfully.']);
        } else {
            $this->errorResponse("Failed to update record.", 500);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->orderableItem->delete($id)) {
            $this->successResponse(['id' => $id, 'message' => 'Record deleted successfully.']);
        } else {
            $this->errorResponse("Failed to delete record.", 500);
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
