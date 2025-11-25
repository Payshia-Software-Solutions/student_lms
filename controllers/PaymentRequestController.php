<?php

require_once __DIR__ . '/../models/PaymentRequest.php';

class PaymentRequestController
{
    private $paymentRequest;
    private $ftp_config;

    // **FIX: Accept FTP config in the constructor**
    public function __construct($pdo, $ftp_config)
    {
        $this->paymentRequest = new PaymentRequest($pdo);
        // **FIX: Use the FTP config that is passed in**
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $stmt = $this->paymentRequest->getAll();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        if ($this->paymentRequest->getById($id)) {
            $record_item = [
                'id' => $this->paymentRequest->id,
                'student_number' => $this->paymentRequest->student_number,
                'slip_url' => $this->paymentRequest->slip_url,
                'payment_amount' => $this->paymentRequest->payment_amount,
                'hash' => $this->paymentRequest->hash,
                'bank' => $this->paymentRequest->bank,
                'branch' => $this->paymentRequest->branch,
                'ref' => $this->paymentRequest->ref,
                'request_status' => $this->paymentRequest->request_status,
                'created_at' => $this->paymentRequest->created_at,
            ];
            echo json_encode(['status' => 'success', 'data' => $record_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function createRecord()
    {
        // --- FTP and File Handling ---
        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');

        if (!isset($_FILES['payment_slip'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No payment slip was uploaded.']);
            return;
        }

        $file = $_FILES['payment_slip'];
        $tmp_path = $file['tmp_name'];
        $image_content = file_get_contents($tmp_path);
        $image_hash = hash('sha256', $image_content);

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP connection failed.']);
            return;
        }
        
        if (ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            ftp_pasv($conn_id, true);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP login failed.']);
            ftp_close($conn_id);
            return;
        }

        // Define the specific directory for payment slips
        $upload_directory_name = 'payment_slips';
        $remote_dir = $ftp_root . '/' . $upload_directory_name;

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
        $data['slip_url'] = $public_url_base . '/' . $upload_directory_name . '/' . $file_name;
        $data['hash'] = $image_hash;

        $newId = $this->paymentRequest->create($data);

        if ($newId) {
            if ($this->paymentRequest->getById($newId)) {
                $record_item = [
                    'id' => $this->paymentRequest->id,
                    'student_number' => $this->paymentRequest->student_number,
                    'slip_url' => $this->paymentRequest->slip_url,
                    'payment_amount' => $this->paymentRequest->payment_amount,
                    'hash' => $this->paymentRequest->hash,
                    'bank' => $this->paymentRequest->bank,
                    'branch' => $this->paymentRequest->branch,
                    'ref' => $this->paymentRequest->ref,
                    'request_status' => $this->paymentRequest->request_status,
                    'created_at' => $this->paymentRequest->created_at,
                ];
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Record created successfully', 'data' => $record_item]);
            } else {
                 http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve created record.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create record']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->paymentRequest->update($id, $data)) {
            if ($this->paymentRequest->getById($id)) {
                 $record_item = [
                    'id' => $this->paymentRequest->id,
                    'student_number' => $this->paymentRequest->student_number,
                    'slip_url' => $this->paymentRequest->slip_url,
                    'payment_amount' => $this->paymentRequest->payment_amount,
                    'hash' => $this->paymentRequest->hash,
                    'bank' => $this->paymentRequest->bank,
                    'branch' => $this->paymentRequest->branch,
                    'ref' => $this->paymentRequest->ref,
                    'request_status' => $this->paymentRequest->request_status,
                    'created_at' => $this->paymentRequest->created_at,
                ];
                echo json_encode(['status' => 'success', 'message' => 'Record updated successfully', 'data' => $record_item]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Record not found after update']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update record']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->paymentRequest->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete record']);
        }
    }
}
