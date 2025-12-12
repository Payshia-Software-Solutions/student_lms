<?php

class CourseBucket
{
    private $conn;

    // Properties
    public $id;
    public $course_id;
    public $name;
    public $description;
    public $payment_type;
    public $payment_amount;
    public $is_active;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS course_bucket (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            payment_type ENUM('monthly', 'yearly', 'one-time') NOT NULL,
            payment_amount DECIMAL(10, 2) NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new course bucket
    public function create($data)
    {
        $query = "INSERT INTO course_bucket (course_id, name, description, payment_type, payment_amount, is_active, created_by, updated_by) VALUES (:course_id, :name, :description, :payment_type, :payment_amount, :is_active, :created_by, :updated_by)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':payment_type', $data['payment_type']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all course buckets
    public function getAll()
    {
        $query = "SELECT * FROM course_bucket";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single course bucket by ID
    public function getById($id)
    {
        $query = "SELECT * FROM course_bucket WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->payment_type = $row['payment_type'];
            $this->payment_amount = $row['payment_amount'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            $this->updated_at = $row['updated_at'];
            $this->updated_by = $row['updated_by'];
            return $row;
        }
        return false;
    }
    
    public function getByFilters($filters)
    {
        $query = "SELECT * FROM course_bucket WHERE 1=1";
        $params = [];

        $allowed_filters = ['id', 'course_id', 'name', 'payment_type', 'is_active'];

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $allowed_filters)) {
                    $query .= " AND `" . $key . "` = :" . $key;
                    $params[':' . $key] = $value;
                }
            }
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error (getByFilters): " . $e->getMessage());
            return [];
        }
    }

    // Get all course buckets for a specific course
    public function getByCourseId($course_id)
    {
        try {
            $query = "SELECT * FROM course_bucket WHERE course_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $course_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    // Update a course bucket
    public function update($id, $data)
    {
        $query = "UPDATE course_bucket SET course_id = :course_id, name = :name, description = :description, payment_type = :payment_type, payment_amount = :payment_amount, is_active = :is_active, updated_by = :updated_by WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':payment_type', $data['payment_type']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a course bucket
    public function delete($id)
    {
        $query = "DELETE FROM course_bucket WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
