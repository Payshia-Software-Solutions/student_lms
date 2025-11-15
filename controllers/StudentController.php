<?php

require_once __DIR__ . '/../models/Student.php';

class StudentController
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
        if (empty($data['username']) || empty($data['firstname']) || empty($data['lastname']) || empty($data['phone_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        try {
            $query = "INSERT INTO students (username, firstname, lastname, date_of_birth, gender, parent_name, phone_number, parent_phone_number, address, city_id, nic, profile_image_url) VALUES (:username, :firstname, :lastname, :date_of_birth, :gender, :parent_name, :phone_number, :parent_phone_number, :address, :city_id, :nic, :profile_image_url)";
            $stmt = $this->db->prepare($query);

            // Bind parameters
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':firstname', $data['firstname']);
            $stmt->bindValue(':lastname', $data['lastname']);
            $stmt->bindValue(':phone_number', $data['phone_number']);
            $stmt->bindValue(':date_of_birth', $data['date_of_birth'] ?? null);
            $stmt->bindValue(':gender', $data['gender'] ?? null);
            $stmt->bindValue(':parent_name', $data['parent_name'] ?? null);
            $stmt->bindValue(':parent_phone_number', $data['parent_phone_number'] ?? null);
            $stmt->bindValue(':address', $data['address'] ?? null);
            $stmt->bindValue(':city_id', $data['city_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':nic', $data['nic'] ?? null);
            $stmt->bindValue(':profile_image_url', $data['profile_image_url'] ?? null);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Student created successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create student.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            // Check for duplicate entry
            if ($e->getCode() == 23000) {
                 echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
    }

    public function getAllRecords()
    {
        try {
            $query = "SELECT * FROM students WHERE deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $students]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getRecordById($id)
    {
        try {
            $query = "SELECT * FROM students WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                echo json_encode(['status' => 'success', 'data' => $student]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
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
            $query = "UPDATE students SET ";
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
                    echo json_encode(['status' => 'success', 'message' => 'Student updated successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Student not found or no changes made.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update student.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            if ($e->getCode() == 23000) {
                 echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
    }

    public function deleteRecord($id)
    {
        try {
            $query = "UPDATE students SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Student deleted successfully.']);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Student not found or already deleted.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete student.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getStudentEnrollments($id)
    {
        try {
            if (!$this->recordExists('students', $id)) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
                return;
            }

            $query = "
                SELECT 
                    c.id, 
                    c.course_name, 
                    c.course_code,
                    c.description,
                    e.enrollment_date,
                    e.grade
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = :id AND e.deleted_at IS NULL
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $courses]);
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
