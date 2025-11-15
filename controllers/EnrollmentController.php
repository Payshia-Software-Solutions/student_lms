<?php

require_once __DIR__ . '/../models/Enrollment.php';

class EnrollmentController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate input data
        if (empty($data['student_id']) || empty($data['course_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: student_id and course_id']);
            return;
        }

        try {
            // Check if the student and course exist
            if (!$this->recordExists('students', $data['student_id']) || !$this->recordExists('courses', $data['course_id'])) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Student or Course not found.']);
                return;
            }

            // Check for duplicate enrollment
            if ($this->isEnrolled($data['student_id'], $data['course_id'])) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'Student is already enrolled in this course.']);
                return;
            }

            $query = "INSERT INTO enrollments (student_id, course_id, enrollment_date, grade) VALUES (:student_id, :course_id, :enrollment_date, :grade)";
            $stmt = $this->db->prepare($query);

            // Bind parameters
            $stmt->bindValue(':student_id', $data['student_id'], PDO::PARAM_INT);
            $stmt->bindValue(':course_id', $data['course_id'], PDO::PARAM_INT);
            $stmt->bindValue(':enrollment_date', $data['enrollment_date'] ?? date('Y-m-d'));
            $stmt->bindValue(':grade', $data['grade'] ?? null);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Enrollment created successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create enrollment.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getAllRecords()
    {
        try {
            $query = "
                SELECT 
                    e.id, 
                    e.student_id, 
                    s.first_name, 
                    s.last_name, 
                    e.course_id, 
                    c.course_name, 
                    e.enrollment_date, 
                    e.grade 
                FROM enrollments e
                JOIN students s ON e.student_id = s.id
                JOIN courses c ON e.course_id = c.id
                WHERE e.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $enrollments]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getRecordById($id)
    {
        try {
            $query = "
                SELECT 
                    e.id, 
                    e.student_id, 
                    s.first_name, 
                    s.last_name, 
                    e.course_id, 
                    c.course_name, 
                    e.enrollment_date, 
                    e.grade 
                FROM enrollments e
                JOIN students s ON e.student_id = s.id
                JOIN courses c ON e.course_id = c.id
                WHERE e.id = :id AND e.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enrollment) {
                echo json_encode(['status' => 'success', 'data' => $enrollment]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Enrollment not found.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No data provided for update.']);
            return;
        }

        try {
            $query = "UPDATE enrollments SET ";
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }
            $query .= implode(', ', $fields);
            $query .= " WHERE id = :id";

            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Enrollment updated successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Enrollment not found or no changes made.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update enrollment.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function deleteRecord($id)
    {
        try {
            $query = "UPDATE enrollments SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Enrollment deleted successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Enrollment not found or already deleted.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete enrollment.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // Helper function to check if a record exists in a table
    private function recordExists($tableName, $id)
    {
        $query = "SELECT id FROM " . $tableName . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    // Helper function to check if a student is already enrolled in a course
    private function isEnrolled($studentId, $courseId)
    {
        $query = "SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}
