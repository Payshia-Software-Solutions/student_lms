<?php

class StudentOrder
{
    private $conn;
    private $table = 'student_order';

    public $id;
    public $student_id;
    public $orderable_item_id;
    public $order_status;
    public $address_line_1;
    public $address_line_2;
    public $city;
    public $district;
    public $postal_code;
    public $phone_number_1;
    public $phone_number_2;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `student_order` (\n            `id` INT AUTO_INCREMENT PRIMARY KEY,\n            `student_id` INT NOT NULL,\n            `orderable_item_id` INT NOT NULL,\n            `order_status` ENUM('pending', 'packed', 'handed_over', 'delivered') NOT NULL DEFAULT 'pending',\n            `address_line_1` VARCHAR(255) NOT NULL,\n            `address_line_2` VARCHAR(255) DEFAULT NULL,\n            `city` VARCHAR(100) NOT NULL,\n            `district` VARCHAR(100) NOT NULL,\n            `postal_code` VARCHAR(20) NOT NULL,\n            `phone_number_1` VARCHAR(20) NOT NULL,\n            `phone_number_2` VARCHAR(20) DEFAULT NULL,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            FOREIGN KEY (student_id) REFERENCES students(id),\n            FOREIGN KEY (orderable_item_id) REFERENCES orderable_item(id)\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error (StudentOrder): " . $e->getMessage());
        }
    }

    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByStudent($student_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE student_id = :student_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_id, orderable_item_id, address_line_1, address_line_2, city, district, postal_code, phone_number_1, phone_number_2) VALUES (:student_id, :orderable_item_id, :address_line_1, :address_line_2, :city, :district, :postal_code, :phone_number_1, :phone_number_2)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':address_line_1', $data['address_line_1']);
        $stmt->bindParam(':address_line_2', $data['address_line_2']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':district', $data['district']);
        $stmt->bindParam(':postal_code', $data['postal_code']);
        $stmt->bindParam(':phone_number_1', $data['phone_number_1']);
        $stmt->bindParam(':phone_number_2', $data['phone_number_2']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateStatus($id, $status)
    {
        $query = 'UPDATE ' . $this->table . ' SET order_status = :order_status WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':order_status', $status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
