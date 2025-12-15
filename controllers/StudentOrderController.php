
<?php

require_once __DIR__ . '/../models/StudentOrder.php';
require_once __DIR__ . '/../models/StudentOrderDetail.php';
require_once __DIR__ . '/../models/PaymentRequest.php';

class StudentOrderController
{
    private $studentOrder;
    private $studentOrderDetail;
    private $paymentRequest;
    private $db;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->db = $pdo;
        $this->ftp_config = $ftp_config;
        $this->studentOrder = new StudentOrder($this->db);
        $this->studentOrderDetail = new StudentOrderDetail($this->db);
        $this->paymentRequest = new PaymentRequest($this->db);
    }

    public function getAllRecords()
    {
        $stmt = $this->studentOrder->getAll();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordsByFilter()
    {
        $filters = $_GET;
        $records = $this->studentOrder->getByFilters($filters);
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        $record = $this->studentOrder->getById($id);
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function createRecord()
    {
        $payment_request_id = null;
        $conn_id = null;

        try {
            $this->db->beginTransaction();

            // --- Handle Payment Slip IF it exists ---
            if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0) {
                // FTP Config and Connection
                $ftp_server = $this->ftp_config['server'];
                $ftp_user = $this->ftp_config['username'];
                $ftp_pass = $this->ftp_config['password'];
                $public_url_base = 'https://student-lms-ftp.payshia.com';

                $conn_id = ftp_connect($ftp_server);
                if (!$conn_id) {
                    throw new Exception("FTP connection failed.");
                }
                if (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
                    throw new Exception("FTP login failed.");
                }
                ftp_pasv($conn_id, true);

                // Hashing
                $file = $_FILES['payment_slip'];
                $image_content = file_get_contents($file['tmp_name']);
                $image_hash = hash('sha256', $image_content);

                // Duplicate Check
                $stmt = $this->paymentRequest->getByFilters(['image_hash' => $image_hash, 'request_status' => 'approved']);
                $existing_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($existing_records) > 0) {
                    throw new Exception("This payment slip has already been used for an approved payment.");
                }

                // Create Payment Request
                $paymentRequestData = [
                    'student_number' => $_POST['student_number'],
                    'request_status' => 'pending',
                    'amount' => $_POST['final_price'],
                    'image_hash' => $image_hash
                ];
                $newPaymentRequestId = $this->paymentRequest->create($paymentRequestData);
                if (!$newPaymentRequestId) {
                    throw new Exception("Unable to create payment request.");
                }
                $payment_request_id = $newPaymentRequestId;

                // FTP Upload
                $remote_file_path = 'payment_slips/' . $newPaymentRequestId . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                if (!ftp_put($conn_id, $remote_file_path, $file['tmp_name'], FTP_BINARY)) {
                    throw new Exception("FTP upload failed.");
                }

                // Update Payment Request with URL
                $file_public_url = $public_url_base . '/' . $remote_file_path;
                $this->paymentRequest->update($newPaymentRequestId, ['slip_url' => $file_public_url]);

                ftp_close($conn_id);
                $conn_id = null; // Reset connection ID
            }

            // --- Create Student Order (Always runs) ---
            $orderData = [
                'student_number' => $_POST['student_number'],
                'payment_request_id' => $payment_request_id,
                'total_order_price' => $_POST['total_order_price'],
                'total_discount' => $_POST['total_discount'],
                'final_price' => $_POST['final_price']
            ];
            $newOrderId = $this->studentOrder->create($orderData);
            if (!$newOrderId) {
                throw new Exception("Unable to create student order.");
            }

            // --- Create Student Order Details (Always runs) ---
            $order_details = json_decode($_POST['order_details'], true);
            foreach ($order_details as $item) {
                $detailData = [
                    'student_order_id' => $newOrderId,
                    'course_id' => $item['course_id'],
                    'course_bucket_id' => $item['course_bucket_id']
                ];
                if (!$this->studentOrderDetail->create($detailData)) {
                    throw new Exception("Unable to create student order detail.");
                }
            }

            $this->db->commit();

            $createdOrder = $this->studentOrder->getById($newOrderId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Order created successfully', 'data' => $createdOrder]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($conn_id) {
                ftp_close($conn_id);
            }
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->studentOrder->update($id, $data)) {
            $record = $this->studentOrder->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Record updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update record']);
        }
    }

    public function deleteRecord($id)
    {
        // Optional: Add logic to handle related records (details, payment requests) if necessary
        if ($this->studentOrder->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete record']);
        }
    }
}
