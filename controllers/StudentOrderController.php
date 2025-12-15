<?php

require_once __DIR__ . '/../models/StudentOrder.php';
require_once __DIR__ . '/../models/PaymentRequest.php';

class StudentOrderController
{
    private $studentOrder;
    private $paymentRequest;
    private $db;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->db = $pdo;
        $this->ftp_config = $ftp_config;
        $this->studentOrder = new StudentOrder($this->db);
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

            $data = json_decode($_POST['data'], true);
            $studentOrderDataFromPost = $data['student_order_data'];

            // --- Handle Payment Slip IF it exists ---
            if (isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0) {

                if (!isset($data['payment_request_data'])) {
                    throw new Exception("Payment slip was uploaded, but payment_request_data was not provided.");
                }
                $paymentRequestDataFromPost = $data['payment_request_data'];

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

                // Check if the payment is for a study pack
                $isStudyPackPayment = isset($paymentRequestDataFromPost['payment_status']) && $paymentRequestDataFromPost['payment_status'] === 'study_pack';

                // Create Payment Request
                $paymentRequestData = [
                    'student_number' => $paymentRequestDataFromPost['student_number'],
                    'slip_url' => 'temp', // Temporary value
                    'payment_amount' => $paymentRequestDataFromPost['payment_amount'],
                    'hash' => $image_hash,
                    'bank' => $paymentRequestDataFromPost['bank'],
                    'branch' => $paymentRequestDataFromPost['branch'],
                    'ref' => $isStudyPackPayment ? 'student_order' : $paymentRequestDataFromPost['ref'],
                    'ref_id' => $isStudyPackPayment ? null : $paymentRequestDataFromPost['ref_id'], // Set ref_id to null for now if it's a study pack
                    'request_status' => 'pending',
                    'payment_status' => $paymentRequestDataFromPost['payment_status'],
                    'course_id' => $paymentRequestDataFromPost['course_id'],
                    'course_bucket_id' => $paymentRequestDataFromPost['course_bucket_id']
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
            $studentOrderDataFromPost['payment_request_id'] = $payment_request_id;
            $newOrderId = $this->studentOrder->create($studentOrderDataFromPost);
            if (!$newOrderId) {
                throw new Exception("Unable to create student order.");
            }

            // --- Link payment to the new order ID if it's a study pack payment ---
            if (isset($isStudyPackPayment) && $isStudyPackPayment) {
                if (!$this->paymentRequest->update($payment_request_id, ['ref_id' => $newOrderId])) {
                    throw new Exception("Unable to link payment request to the new order.");
                }
            }

            $this->db->commit();

            $createdOrder = $this->studentOrder->read_single($newOrderId);
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
