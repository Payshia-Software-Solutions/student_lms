<?php

require_once __DIR__ . '/../models/Course.php';

class CourseController
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
        if (empty($data['course_name']) || !isset($data['credits'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields: course_name and credits']);
            return;
        }

        try {
            $query = "INSERT INTO courses (course_name, description, credits) VALUES (:course_name, :description, :credits)";
            $stmt = $this->db->prepare($query);

            // Bind parameters
            $stmt->bindValue(':course_name', $data['course_name']);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->bindValue(':credits', $data['credits'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Course created successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create course.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getAllRecords()
    {
        try {
            $query = "SELECT * FROM courses WHERE deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $courses]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getRecordById($id)
    {
        try {
            $query = "SELECT * FROM courses WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($course) {
                echo json_encode(['status' => 'success', 'data' => $course]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Course not found.']);
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
            $query = "UPDATE courses SET ";
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
                    echo json_encode(['status' => 'success', 'message' => 'Course updated successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Course not found or no changes made.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update course.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function deleteRecord($id)
    {
        try {
            $query = "UPDATE courses SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Course not found or already deleted.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete course.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getCourseEnrollments($id)
    {
        try {
            if (!$this->recordExists('courses', $id)) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Course not found.']);
                return;
            }

            $query = "
                SELECT 
                    s.id, 
                    s.first_name, 
                    s.last_name, 
                    s.username, 
                    e.enrollment_date, 
                    e.grade 
                FROM enrollments e
                JOIN students s ON e.student_id = s.id
                WHERE e.course_id = :id AND e.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    private function recordExists($tableName, $id)
    {
        $query = "SELECT id FROM " . $tableName . " WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}
