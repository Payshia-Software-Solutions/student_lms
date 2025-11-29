<?php

class Enrollment
{
    private $conn;
    private $table_name = 'enrollments';

    // Properties
    public $id;
    public $student_id;
    public $course_id;
    public $enrollment_date;
    public $grade;
    public $status; // New column
    public $created_at;
    public $updated_at;
    public $deleted_at;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS enrollments (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            student_id INT NOT NULL,\n            course_id INT NOT NULL,\n            enrollment_date DATE,\n            grade VARCHAR(2),\n            status ENUM('pending', 'rejected', 'approved') DEFAULT 'pending',\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL,\n            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,\n            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error (Enrollments): " . $e->getMessage());
        }
    }

    // Create a new enrollment
    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (student_id, course_id, enrollment_date, status) VALUES (:student_id, :course_id, CURDATE(), :status)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':status', $data['status']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all enrollments
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single enrollment by ID
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update an enrollment's status
    public function updateStatus($id, $status)
    {
        // Validate status
        $allowed_statuses = ['pending', 'rejected', 'approved'];
        if (!in_array($status, $allowed_statuses)) {
            return false; // Invalid status
        }

        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete an enrollment (soft delete)
    public function delete($id)
    {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
