<?php

require_once __DIR__ . '/../models/StudentOrder.php';
require_once __DIR__ . '/../models/PaymentRequest.php';

class StudentOrderController
{
    private $pdo;
    private $studentOrder;
    private $paymentRequest;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->pdo = $pdo;
        $this->studentOrder = new StudentOrder($pdo);
        $this->paymentRequest = new PaymentRequest($pdo);
        $this->ftp_config = $ftp_config;
    }

    public function getLatestOrderByStudent()
    {
        if (!isset($_GET['student_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'student_number is required.']);
            return;
        }

        $student_number = $_GET['student_number'];
        $latest_order = $this->studentOrder->getLatestByStudentNumber($student_number);

        if ($latest_order) {
            echo json_encode(['status' => 'success', 'data' => $latest_order]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'No orders found for this student.']);
        }
    }

    public function getAllRecords()
    {
        $stmt = $this->studentOrder->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordsByFilter()
    {
        $filters = [];
        if(isset($_GET['course_id'])) $filters['course_id'] = $_GET['course_id'];
        if(isset($_GET['course_bucket_id'])) $filters['course_bucket_id'] = $_GET['course_bucket_id'];
        if(isset($_GET['order_status'])) $filters['status'] = $_GET['order_status'];
        if(isset($_GET['student_number'])) $filters['student_number'] = $_GET['student_number'];

        $stmt = $this->studentOrder->getFiltered($filters);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        $record = $this->studentOrder->read_single($id);
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function createRecord()
    {
        // --- FTP and File Handling (Must happen before transaction) ---
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
        $studentOrderData = $data['student_order_data'];
        $paymentRequestData = $data['payment_request_data'];

        $paymentRequestData['slip_url'] = $public_url_base . '/' . $upload_directory_name . '/' . $file_name;
        $paymentRequestData['hash'] = $image_hash;

        try {
            $this->pdo->beginTransaction();

            $studentOrderId = $this->studentOrder->create($studentOrderData);
            if (!$studentOrderId) {
                throw new Exception("Failed to create student order.");
            }

            $paymentRequestData['ref_id'] = $studentOrderId;
            $paymentRequestData['ref'] = 'student_order';

            $paymentRequestId = $this->paymentRequest->create($paymentRequestData);
            if (!$paymentRequestId) {
                throw new Exception("Failed to create payment request.");
            }

            $this->pdo->commit();

            $record = $this->studentOrder->read_single($studentOrderId);

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Order and payment request created successfully.',
                'data' => $record
            ]);

        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->studentOrder->update($id, $data)) {
            $record = $this->studentOrder->read_single($id);
            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update record']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentOrder->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete record']);
        }
    }
}
