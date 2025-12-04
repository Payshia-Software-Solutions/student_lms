<?php

class StudentOrder
{
    private $conn;
    private $table = 'student_order_table';

    public $id;
    public $student_number;
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
        $query = "CREATE TABLE IF NOT EXISTS `student_order_table` (\n            `id` INT AUTO_INCREMENT PRIMARY KEY,\n            `student_number` VARCHAR(50) NOT NULL,\n            `orderable_item_id` INT NOT NULL,\n            `order_status` ENUM('pending', 'packed', 'handed_over', 'delivered') NOT NULL DEFAULT 'pending',\n            `address_line_1` VARCHAR(255) NOT NULL,\n            `address_line_2` VARCHAR(255) DEFAULT NULL,\n            `city` VARCHAR(100) NOT NULL,\n            `district` VARCHAR(100) NOT NULL,\n            `postal_code` VARCHAR(20) NOT NULL,\n            `phone_number_1` VARCHAR(20) NOT NULL,\n            `phone_number_2` VARCHAR(20) DEFAULT NULL,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n        );";

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

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_number, orderable_item_id, order_status, address_line_1, address_line_2, city, district, postal_code, phone_number_1, phone_number_2) VALUES (:student_number, :orderable_item_id, :order_status, :address_line_1, :address_line_2, :city, :district, :postal_code, :phone_number_1, :phone_number_2)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':order_status', $data['order_status']);
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

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET student_number = :student_number, orderable_item_id = :orderable_item_id, order_status = :order_status, address_line_1 = :address_line_1, address_line_2 = :address_line_2, city = :city, district = :district, postal_code = :postal_code, phone_number_1 = :phone_number_1, phone_number_2 = :phone_number_2 WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':order_status', $data['order_status']);
        $stmt->bindParam(':address_line_1', $data['address_line_1']);
        $stmt->bindParam(':address_line_2', $data['address_line_2']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':district', $data['district']);
        $stmt->bindParam(':postal_code', $data['postal_code']);
        $stmt->bindParam(':phone_number_1', $data['phone_number_1']);
        $stmt->bindParam(':phone_number_2', $data['phone_number_2']);

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
