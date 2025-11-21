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
    public $request_status;
    public $created_at;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS payment_request (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_number VARCHAR(50) NOT NULL,
            slip_url VARCHAR(255) NOT NULL,
            payment_amount DECIMAL(10,2) NOT NULL,
            hash VARCHAR(255) NOT NULL,
            bank VARCHAR(100) NOT NULL,
            branch VARCHAR(100) NOT NULL,
            ref VARCHAR(100) NOT NULL,
            request_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
        $query = "INSERT INTO payment_request (student_number, slip_url, payment_amount, hash, bank, branch, ref, request_status) VALUES (:student_number, :slip_url, :payment_amount, :hash, :bank, :branch, :ref, :request_status)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':slip_url', $data['slip_url']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':hash', $data['hash']);
        $stmt->bindParam(':bank', $data['bank']);
        $stmt->bindParam(':branch', $data['branch']);
        $stmt->bindParam(':ref', $data['ref']);
        $stmt->bindParam(':request_status', $data['request_status']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all records
    public function getAll()
    {
        $query = "SELECT * FROM payment_request";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single record by ID
    public function getById($id)
    {
        $query = "SELECT * FROM payment_request WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->student_number = $row['student_number'];
            $this->slip_url = $row['slip_url'];
            $this->payment_amount = $row['payment_amount'];
            $this->hash = $row['hash'];
            $this->bank = $row['bank'];
            $this->branch = $row['branch'];
            $this->ref = $row['ref'];
            $this->request_status = $row['request_status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Update a record
    public function update($id, $data)
    {
        $query = "UPDATE payment_request SET student_number = :student_number, slip_url = :slip_url, payment_amount = :payment_amount, hash = :hash, bank = :bank, branch = :branch, ref = :ref, request_status = :request_status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':slip_url', $data['slip_url']);
        $stmt->bindParam(':payment_amount', $data['payment_amount']);
        $stmt->bindParam(':hash', $data['hash']);
        $stmt->bindParam(':bank', $data['bank']);
        $stmt->bindParam(':branch', $data['branch']);
        $stmt->bindParam(':ref', $data['ref']);
        $stmt->bindParam(':request_status', $data['request_status']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
