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
    public $course_name;
    public $course_bucket_name;

    // Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS student_payment_course (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            course_id INT NOT NULL,\n            course_bucket_id INT NOT NULL,\n            student_number VARCHAR(255) NOT NULL,\n            payment_request_id INT,\n            payment_amount DECIMAL(10, 2) NOT NULL,\n            discount_amount DECIMAL(10, 2) DEFAULT 0,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            FOREIGN KEY (course_id) REFERENCES courses(id),\n            FOREIGN KEY (course_bucket_id) REFERENCES course_bucket(id),\n            FOREIGN KEY (student_number) REFERENCES users(student_number)\n        )";
        $stmt = $db->prepare($query);
        $stmt->execute();
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
        $query = "SELECT
                    spc.*,
                    c.course_name AS course_name,
                    cb.name AS course_bucket_name
                FROM
                    student_payment_course spc
                LEFT JOIN
                    courses c ON spc.course_id = c.id
                LEFT JOIN
                    course_bucket cb ON spc.course_bucket_id = cb.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get records by filters
    public function getByFilters($filters)
    {
        $query = "SELECT
                    spc.*,
                    c.course_name AS course_name,
                    cb.name AS course_bucket_name,
                    cb.payment_amount AS course_bucket_price
                FROM
                    student_payment_course spc
                LEFT JOIN
                    courses c ON spc.course_id = c.id
                LEFT JOIN
                    course_bucket cb ON spc.course_bucket_id = cb.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['course_id'])) {
            $query .= " AND spc.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }

        if (isset($filters['course_bucket_id'])) {
            $query .= " AND spc.course_bucket_id = :course_bucket_id";
            $params[':course_bucket_id'] = $filters['course_bucket_id'];
        }

        if (isset($filters['student_number'])) {
            $query .= " AND spc.student_number = :student_number";
            $params[':student_number'] = $filters['student_number'];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // Get single record by ID
    public function getById($id)
    {
        $query = "SELECT
                    spc.*,
                    c.course_name AS course_name,
                    cb.name AS course_bucket_name
                FROM
                    student_payment_course spc
                LEFT JOIN
                    courses c ON spc.course_id = c.id
                LEFT JOIN
                    course_bucket cb ON spc.course_bucket_id = cb.id
                WHERE spc.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->course_bucket_id = $row['course_bucket_id'];
            $this->student_number = $row['student_number'];
            $this->payment_request_id = $row['payment_request_id'];
            $this->payment_amount = $row['payment_amount'];
            $this->discount_amount = $row['discount_amount'];
            $this->created_at = $row['created_at'];
            $this->course_name = $row['course_name'];
            $this->course_bucket_name = $row['course_bucket_name'];
            return true;
        }
        return false;
    }

    // Update a record
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE student_payment_course SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute($params)) {
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
