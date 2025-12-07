<?php

require_once __DIR__ . '/../models/PaymentRequest.php';

class PaymentRequestController
{
    private $paymentRequest;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->paymentRequest = new PaymentRequest($pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getAllRecords()
    {
        $stmt = $this->paymentRequest->getAll();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordsByFilter()
    {
        if (!isset($_GET['course_id']) || !isset($_GET['course_bucket_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'course_id and course_bucket_id are required.']);
            return;
        }

        $filters = [];
        $filters['course_id'] = $_GET['course_id'];
        $filters['course_bucket_id'] = $_GET['course_bucket_id'];

        if (isset($_GET['student_number'])) {
            $filters['student_number'] = $_GET['student_number'];
        }
        if (isset($_GET['request_status'])) {
            $filters['request_status'] = $_GET['request_status'];
        }

        $stmt = $this->paymentRequest->getByFilters($filters);
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
                'course_id' => $this->paymentRequest->course_id,
                'course_bucket_id' => $this->paymentRequest->course_bucket_id,
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
        $ftp_user = $this->ftp_config['username'];
        $ftp_pass = $this->ftp_config['password'];
        $public_url_base = 'https://student-lms-ftp.payshia.com';

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

        $upload_directory_name = 'payment_slips';
        
        // Attempt to create the directory. The '@' suppresses errors if it already exists.
        @ftp_mkdir($conn_id, $upload_directory_name);

        $file_name = uniqid() . '-' . basename($file['name']);
        $remote_path = $upload_directory_name . '/' . $file_name;

        if (!ftp_put($conn_id, $remote_path, $tmp_path, FTP_BINARY)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP file upload failed.']);
            ftp_close($conn_id);
            return;
        }

        ftp_close($conn_id);

        // --- End of FTP Handling ---

        $data = json_decode($_POST['data'], true);
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
                    'course_id' => $this->paymentRequest->course_id,
                    'course_bucket_id' => $this->paymentRequest->course_bucket_id,
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
                    'course_id' => $this->paymentRequest->course_id,
                    'course_bucket_id' => $this->paymentRequest->course_bucket_id,
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
