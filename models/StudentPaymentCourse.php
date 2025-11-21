<?php

class StudentPaymentCourse
{
    private $conn;

    // Properties
    public $id;
    public $course_id;
    public $course_bucket_id;
    public $student_number;
    public $payment_request_id;
    public $payment_amount;
    public $discount_amount;
    public $created_at;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS student_payment_course (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            course_bucket_id INT NOT NULL,
            student_number VARCHAR(50) NOT NULL,
            payment_request_id INT NOT NULL,
            payment_amount DECIMAL(10,2) NOT NULL,
            discount_amount DECIMAL(10,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (course_id) REFERENCES courses(id),
            FOREIGN KEY (course_bucket_id) REFERENCES course_bucket(id),
            FOREIGN KEY (student_number) REFERENCES users(student_number)
        )";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new record
    public function create($data)
    {
        $query = "INSERT INTO student_payment_course (course_id, course_bucket_id, student_number, payment_request_id, payment_amount, discount_amount) VALUES (:course_id, :course_bucket_id, :student_number, :payment_request_id, :payment_amount, :discount_amount)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':payment_request_id', $data['payment_request_id']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':discount_amount', $data['discount_amount']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all records
    public function getAll()
    {
        $query = "SELECT * FROM student_payment_course";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single record by ID
    public function getById($id)
    {
        $query = "SELECT * FROM student_payment_course WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->course_bucket_id = $row['course_bucket_id'];
            $this->student_number = $row['student_number'];
            $this->payment_request_id = $row['payment_request_id'];
            $this->payment_amount = $row['payment_amount'];
            $this->discount_amount = $row['discount_amount'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Update a record
    public function update($id, $data)
    {
        $query = "UPDATE student_payment_course SET course_id = :course_id, course_bucket_id = :course_bucket_id, student_number = :student_number, payment_request_id = :payment_request_id, payment_amount = :payment_amount, discount_amount = :discount_amount WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':payment_request_id', $data['payment_request_id']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':discount_amount', $data['discount_amount']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a record
    public function delete($id)
    {
        $query = "DELETE FROM student_payment_course WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
