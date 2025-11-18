<?php

class Course
{
    private $conn;

    // Properties
    public $id;
    public $course_name;
    public $course_code;
    public $description;
    public $credits;
    public $payment_status;
    public $enrollment_key;
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
        $query = "CREATE TABLE IF NOT EXISTS courses (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            course_name VARCHAR(255) NOT NULL,\n            course_code VARCHAR(50) UNIQUE NOT NULL,\n            description TEXT,\n            credits INT NOT NULL,\n            payment_status ENUM('monthly', 'year', 'once') NOT NULL DEFAULT 'monthly',\n            enrollment_key VARCHAR(5) NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new course
    public function create($data)
    {
        $query = "INSERT INTO courses (course_name, course_code, description, credits, payment_status, enrollment_key) VALUES (:course_name, :course_code, :description, :credits, :payment_status, :enrollment_key)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->course_name = htmlspecialchars(strip_tags($data['course_name']));
        $this->course_code = htmlspecialchars(strip_tags($data['course_code']));
        $this->description = htmlspecialchars(strip_tags($data['description']));
        $this->credits = htmlspecialchars(strip_tags($data['credits']));
        $this->payment_status = htmlspecialchars(strip_tags($data['payment_status']));
        $this->enrollment_key = isset($data['enrollment_key']) ? htmlspecialchars(strip_tags($data['enrollment_key'])) : null;

        $stmt->bindParam(':course_name', $this->course_name);
        $stmt->bindParam(':course_code', $this->course_code);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':credits', $this->credits);
        $stmt->bindParam(':payment_status', $this->payment_status);
        $stmt->bindParam(':enrollment_key', $this->enrollment_key);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all courses
    public function getAll()
    {
        $query = "SELECT * FROM courses WHERE deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single course by ID
    public function getById($id)
    {
        $query = "SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->course_name = $row['course_name'];
            $this->course_code = $row['course_code'];
            $this->description = $row['description'];
            $this->credits = $row['credits'];
            $this->payment_status = $row['payment_status'];
            $this->enrollment_key = $row['enrollment_key'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update a course
    public function update($id, $data)
    {
        $query = "UPDATE courses SET course_name = :course_name, course_code = :course_code, description = :description, credits = :credits, payment_status = :payment_status, enrollment_key = :enrollment_key WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->id = htmlspecialchars(strip_tags($id));
        $this->course_name = htmlspecialchars(strip_tags($data['course_name']));
        $this->course_code = htmlspecialchars(strip_tags($data['course_code']));
        $this->description = htmlspecialchars(strip_tags($data['description']));
        $this->credits = htmlspecialchars(strip_tags($data['credits']));
        $this->payment_status = htmlspecialchars(strip_tags($data['payment_status']));
        $this->enrollment_key = isset($data['enrollment_key']) ? htmlspecialchars(strip_tags($data['enrollment_key'])) : null;


        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':course_name', $this->course_name);
        $stmt->bindParam(':course_code', $this->course_code);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':credits', $this->credits);
        $stmt->bindParam(':payment_status', $this->payment_status);
        $stmt->bindParam(':enrollment_key', $this->enrollment_key);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a course (soft delete)
    public function delete($id)
    {
        $query = "UPDATE courses SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
