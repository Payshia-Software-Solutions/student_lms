<?php

require_once __DIR__ . '/../models/UserFullDetails.php';
require_once __DIR__ . '/../models/StudentPaymentCourse.php'; // Added dependency

class UserFullDetailsController
{
    private $userFullDetails;
    private $studentPaymentCourse; // Added property
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->userFullDetails = new UserFullDetails($this->db);
        $this->studentPaymentCourse = new StudentPaymentCourse($this->db); // Initialized model
    }

    public function getUserWithCourseDetails()
    {
        if (!isset($_GET['student_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            return;
        }

        $student_number = $_GET['student_number'];

        $user_data = $this->userFullDetails->read_single_by_student_number($student_number);

        if (!$user_data) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        $user_data['courses'] = $this->userFullDetails->getStudentCoursesWithDetails($student_number);

        // --- Start of new implementation: Get Payment Balance and History ---
        foreach ($user_data['courses'] as &$course) { // Use reference to modify array directly
            if (isset($course['course_buckets']) && is_array($course['course_buckets'])) {
                foreach ($course['course_buckets'] as &$bucket) { // Use reference here as well
                    $bucket_id = $bucket['id'];
                    $bucket_price = (float)$bucket['course_bucket_price'];

                    $paymentFilters = [
                        'student_number' => $student_number,
                        'course_bucket_id' => $bucket_id
                    ];

                    $payments = $this->studentPaymentCourse->getByFilters($paymentFilters);
                    
                    $total_paid = 0;
                    if ($payments) {
                        $total_paid = array_sum(array_column($payments, 'payment_amount'));
                    }

                    $balance = $bucket_price - $total_paid;

                    // Inject the payment details and history into the bucket
                    $bucket['payment_details'] = [
                        'course_bucket_price' => $bucket_price,
                        'total_paid_amount' => $total_paid,
                        'balance' => $balance,
                        'payments' => $payments ?: [] // Include the list of payments
                    ];
                }
                unset($bucket); // Unset reference to avoid side effects
            }
        }
        unset($course); // Unset reference
        // --- End of new implementation ---

        echo json_encode(['status' => 'success', 'data' => $user_data]);
    }

    // ... (rest of the functions: createUser, updateUser, etc. remain unchanged)
    public function createUserAndDetails()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['user_data']) || !isset($data['user_details_data'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing user_data or user_details_data']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Create user
            $userId = $this->userFullDetails->createUser($data['user_data']);

            // Create user details
            $this->userFullDetails->createUserDetails($userId, $data['user_details_data']);

            $this->db->commit();

            $newUser = $this->userFullDetails->read_single_by_id($userId);

            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'User and details created successfully', 'data' => $newUser]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create user and details: ' . $e->getMessage()]);
        }
    }

    public function updateUserAndDetails($student_number)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $this->db->beginTransaction();

            $this->userFullDetails->updateUserAndDetailsByStudentNumber($student_number, $data);

            $this->db->commit();

            $updatedUser = $this->userFullDetails->read_single_by_student_number($student_number);
            echo json_encode(['status' => 'success', 'message' => 'User and details updated successfully', 'data' => $updatedUser]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user and details: ' . $e->getMessage()]);
        }
    }

    public function getRecordByStudentNumber($studentNumber)
    {
        $record = $this->userFullDetails->read_single_by_student_number($studentNumber);
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function getAllRecords()
    {
        $records = $this->userFullDetails->read();
        echo json_encode(['status' => 'success', 'data' => $records]);
    }

    public function getRecordById($id)
    {
        $record = $this->userFullDetails->read_single_by_id($id);
        if ($record) {
            echo json_encode(['status' => 'success', 'data' => $record]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userFullDetails->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'User details updated.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'User details could not be updated.']);
        }
    }

    public function deleteRecord($id)
    {
        // You might want to add more complex logic here, like checking for related records
        // before deleting, or using a soft delete.
        if ($this->userFullDetails->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User details deleted.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'User details could not be deleted.']);
        }
    }
}
