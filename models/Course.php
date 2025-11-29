
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
    public $course_fee;
    public $registration_fee;
    public $img_url;
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
        $query = "CREATE TABLE IF NOT EXISTS courses (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            course_name VARCHAR(255) NOT NULL,\n            course_code VARCHAR(50) UNIQUE NOT NULL,\n            description TEXT,\n            credits INT NOT NULL,\n            payment_status ENUM('monthly', 'year', 'once') NOT NULL DEFAULT 'monthly',\n            enrollment_key VARCHAR(5) NULL,\n            course_fee DECIMAL(10, 2) DEFAULT 0.00,\n            registration_fee DECIMAL(10, 2) DEFAULT 0.00,\n            img_url VARCHAR(255) DEFAULT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL\n        );";

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
        $query = "INSERT INTO courses (course_name, course_code, description, credits, payment_status, enrollment_key, course_fee, registration_fee, img_url) VALUES (:course_name, :course_code, :description, :credits, :payment_status, :enrollment_key, :course_fee, :registration_fee, :img_url)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':course_name', htmlspecialchars(strip_tags($data['course_name'])));
        $stmt->bindParam(':course_code', htmlspecialchars(strip_tags($data['course_code'])));
        $stmt->bindParam(':description', htmlspecialchars(strip_tags($data['description'])));
        $stmt->bindParam(':credits', htmlspecialchars(strip_tags($data['credits'])));
        $stmt->bindParam(':payment_status', htmlspecialchars(strip_tags($data['payment_status'])));
        $stmt->bindParam(':enrollment_key', htmlspecialchars(strip_tags($data['enrollment_key'])));
        $stmt->bindParam(':course_fee', htmlspecialchars(strip_tags($data['course_fee'])));
        $stmt->bindParam(':registration_fee', htmlspecialchars(strip_tags($data['registration_fee'])));
        $stmt->bindParam(':img_url', htmlspecialchars(strip_tags($data['img_url'])));

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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single course by ID
    public function getById($id)
    {
        $query = "SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update a course dynamically
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed_fields = ['course_name', 'course_code', 'description', 'credits', 'payment_status', 'enrollment_key', 'course_fee', 'registration_fee', 'img_url'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($fields)) {
            return false; // No valid fields to update
        }

        $query = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    // Delete a course (soft delete)
    public function delete($id)
    {
        $query = "UPDATE courses SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        return $stmt->execute();
    }
}
