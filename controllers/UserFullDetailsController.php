<?php

require_once __DIR__ . '/../models/UserFullDetails.php';
require_once __DIR__ . '/../models/StudentPaymentCourse.php';

class UserFullDetailsController
{
    private $userFullDetails;
    private $studentPaymentCourse;
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->userFullDetails = new UserFullDetails($this->db);
        $this->studentPaymentCourse = new StudentPaymentCourse($this->db);
    }

    // --- The new, self-contained function to get course details ---
    private function getStudentCoursesWithDetails($student_number)
    {
        $query = "
            SELECT 
                c.id as course_id, c.course_name, c.description as course_description, c.image as course_image,
                cb.id as course_bucket_id, cb.course_bucket_name, cb.course_bucket_price,
                cc.id as course_content_id, cc.name as course_content_name, cc.type as course_content_type, cc.is_free,
                scp.id as progress_id, scp.status as progress_status
            FROM 
                student_course sc
            JOIN 
                course c ON sc.course_id = c.id
            LEFT JOIN 
                course_bucket cb ON cb.course_id = c.id
            LEFT JOIN 
                course_content cc ON cc.course_bucket_id = cb.id
            LEFT JOIN 
                student_content_progress scp ON scp.course_content_id = cc.id AND scp.student_number = :student_number
            WHERE 
                sc.student_number = :student_number
            ORDER BY
                c.id, cb.id, cc.id;
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $courses = [];

        foreach ($results as $row) {
            if (!$row['course_id']) continue;

            if (!isset($courses[$row['course_id']])) {
                $courses[$row['course_id']] = [
                    'id' => $row['course_id'],
                    'course_name' => $row['course_name'],
                    'description' => $row['course_description'],
                    'image' => $row['course_image'],
                    'course_buckets' => []
                ];
            }

            if ($row['course_bucket_id'] && !isset($courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']])) {
                $courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']] = [
                    'id' => $row['course_bucket_id'],
                    'course_bucket_name' => $row['course_bucket_name'],
                    'course_bucket_price' => $row['course_bucket_price'],
                    'course_contents' => []
                ];
            }

            if ($row['course_content_id']) {
                $courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']]['course_contents'][] = [
                    'id' => $row['course_content_id'],
                    'course_content_name' => $row['course_content_name'],
                    'course_content_type' => $row['course_content_type'],
                    'is_free' => $row['is_free'],
                    'progress' => $row['progress_id'] ? [
                        'id' => $row['progress_id'],
                        'status' => $row['progress_status']
                    ] : null
                ];
            }
        }

        return array_values(array_map(function($course) {
            if (isset($course['course_buckets'])) {
                $course['course_buckets'] = array_values($course['course_buckets']);
            }
            return $course;
        }, $courses));
    }

    public function getUserWithCourseDetails()
    {
        if (!isset($_GET['student_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            return;
        }

        $student_number = $_GET['student_number'];

        $user_data = $this->userFullDetails->read_by_student_number($student_number);

        if (!$user_data) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        // --- FIX: Call the local function instead of the model's ---
        $user_data['courses'] = $this->getStudentCoursesWithDetails($student_number);

        foreach ($user_data['courses'] as &$course) {
            if (isset($course['course_buckets']) && is_array($course['course_buckets'])) {
                foreach ($course['course_buckets'] as &$bucket) {
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

                    $bucket['payment_details'] = [
                        'course_bucket_price' => $bucket_price,
                        'total_paid_amount' => $total_paid,
                        'balance' => $balance,
                        'payments' => $payments ?: []
                    ];
                }
                unset($bucket);
            }
        }
        unset($course);

        echo json_encode(['status' => 'success', 'data' => $user_data]);
    }

    // ... (rest of the original functions are preserved) ...
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
            $userId = $this->userFullDetails->createUser($data['user_data']);
            $this->userFullDetails->createUserDetails($userId, $data['user_details_data']);
            $this->db->commit();

            $newUser = $this->userFullDetails->read_single($userId); 

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

            $updatedUser = $this->userFullDetails->read_by_student_number($student_number);
            echo json_encode(['status' => 'success', 'message' => 'User and details updated successfully', 'data' => $updatedUser]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user and details: ' . $e->getMessage()]);
        }
    }

    public function getRecordByStudentNumber($studentNumber)
    {
        $record = $this->userFullDetails->read_by_student_number($studentNumber);
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
        $record = $this->userFullDetails->read_single($id);
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
        if ($this->userFullDetails->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User details deleted.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'User details could not be deleted.']);
        }
    }
}
