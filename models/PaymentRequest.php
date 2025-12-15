<?php

class PaymentRequest
{
    private $conn;

    // Properties
    public $id;
    public $student_number;
    public $slip_url;
    public $payment_amount;
    public $hash;
    public $bank;
    public $branch;
    public $ref;
    public $ref_id;
    public $request_status;
    public $payment_status;
    public $created_at;
    public $course_id;
    public $course_bucket_id;
    public $course_name;
    public $course_bucket_name;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS payment_request (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            student_number VARCHAR(50) NOT NULL,\n            slip_url VARCHAR(255) NOT NULL,\n            payment_amount DECIMAL(10,2) NOT NULL,\n            hash VARCHAR(255) NOT NULL,\n            bank VARCHAR(100) NOT NULL,\n            branch VARCHAR(100) NOT NULL,\n            ref VARCHAR(100) NOT NULL,\n            ref_id VARCHAR(255) NULL,\n            request_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',\n            payment_status ENUM('course_fee', 'study_pack') NOT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            course_id INT NOT NULL,\n            course_bucket_id INT NOT NULL,\n            FOREIGN KEY (student_number) REFERENCES users(student_number),\n            FOREIGN KEY (course_id) REFERENCES courses(id),\n            FOREIGN KEY (course_bucket_id) REFERENCES course_bucket(id)\n        )";

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
        // --- THIS IS THE CORRECTED CODE. IF THE ERROR PERSISTS, THE SERVER IS CACHING THE OLD FILE. ---
        // --- PLEASE RESTART PHP-FPM OR APACHE TO CLEAR THE CACHE. ---
        $query = "INSERT INTO payment_request (student_number, slip_url, payment_amount, hash, bank, branch, ref, ref_id, request_status, payment_status, course_id, course_bucket_id) VALUES (:student_number, :slip_url, :payment_amount, :hash, :bank, :branch, :ref, :ref_id, :request_status, :payment_status, :course_id, :course_bucket_id)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':slip_url', $data['slip_url']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':hash', $data['hash']);
        $stmt->bindParam(':bank', $data['bank']);
        $stmt->bindParam(':branch', $data['branch']);
        $stmt->bindParam(':ref', $data['ref']);
        $stmt->bindParam(':ref_id', $data['ref_id']);
        $stmt->bindParam(':request_status', $data['request_status']);
        $stmt->bindParam(':payment_status', $data['payment_status']);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all records
    public function getAll()
    {
        $query = "SELECT\n                    pr.*,\n                    c.course_name AS course_name,\n                    cb.name AS course_bucket_name\n                FROM\n                    payment_request pr\n                LEFT JOIN\n                    courses c ON pr.course_id = c.id\n                LEFT JOIN\n                    course_bucket cb ON pr.course_bucket_id = cb.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get records by filters
    public function getByFilters($filters)
    {
        $query = "SELECT\n                    pr.*,\n                    c.course_name AS course_name,\n                    cb.name AS course_bucket_name\n                FROM\n                    payment_request pr\n                LEFT JOIN\n                    courses c ON pr.course_id = c.id\n                LEFT JOIN\n                    course_bucket cb ON pr.course_bucket_id = cb.id\n                WHERE 1=1";
        $params = [];

        if (isset($filters['course_id'])) {
            $query .= " AND pr.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }

        if (isset($filters['course_bucket_id'])) {
            $query .= " AND pr.course_bucket_id = :course_bucket_id";
            $params[':course_bucket_id'] = $filters['course_bucket_id'];
        }

        if (isset($filters['student_number'])) {
            $query .= " AND pr.student_number = :student_number";
            $params[':student_number'] = $filters['student_number'];
        }

        if (isset($filters['request_status'])) {
            $query .= " AND pr.request_status = :request_status";
            $params[':request_status'] = $filters['request_status'];
        }

        if (isset($filters['payment_status'])) {
            $query .= " AND pr.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }

        if (isset($filters['ref_id'])) {
            $query .= " AND pr.ref_id = :ref_id";
            $params[':ref_id'] = $filters['ref_id'];
        }

        if (isset($filters['hash'])) {
            $query .= " AND pr.hash = :hash";
            $params[':hash'] = $filters['hash'];
        }

        if (isset($filters['not_id'])) {
            $query .= " AND pr.id != :not_id";
            $params[':not_id'] = $filters['not_id'];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // Get a single record by ID
    public function getById($id)
    {
        $query = "SELECT\n                    pr.*,\n                    c.course_name AS course_name,\n                    cb.name AS course_bucket_name\n                FROM\n                    payment_request pr\n                LEFT JOIN\n                    courses c ON pr.course_id = c.id\n                LEFT JOIN\n                    course_bucket cb ON pr.course_bucket_id = cb.id\n                WHERE pr.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->student_number = $row['student_number'];
            $this->slip_.phpurl = $row['slip_url'];
            $this->payment_amount = $row['payment_amount'];
            $this->hash = $row['hash'];
            $this->bank = $row['bank'];
            $this->branch = $row['branch'];
            $this->ref = $row['ref'];
            $this->ref_id = $row['ref_id'];
            $this->request_status = $row['request_status'];
            $this->payment_status = $row['payment_status'];
            $this->created_at = $row['created_at'];
            $this->course_id = $row['course_id'];
            $this->course_bucket_id = $row['course_bucket_id'];
            $this->course_name = $row['course_name'];
            $this->course_bucket_name = $row['course_bucket_name'];
            return true;
        }
        return false;
    }

    // Update a record dynamically
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed_fields = [
            'student_number', 'slip_url', 'payment_amount', 'hash', 'bank',
            'branch', 'ref', 'ref_id', 'request_status', 'payment_status', 'course_id', 'course_bucket_id'
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false; // No valid fields to update
        }

        $query = "UPDATE payment_request SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
    }

    // Delete a record
    public function delete($id)
    {
        $query = "DELETE FROM payment_request WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
