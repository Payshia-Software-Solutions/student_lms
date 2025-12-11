<?php

class StudentOrder
{
    private $conn;
    private $table = 'student_order_table';

    public $id;
    public $student_number;
    public $orderable_item_id;
    public $order_status;
    public $tracking_number;
    public $cod_amount;
    public $package_weight;
    public $order_date;
    public $delivery_date;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `student_order_table` (\n            `id` INT AUTO_INCREMENT PRIMARY KEY,\n            `student_number` VARCHAR(50) NOT NULL,\n            `orderable_item_id` INT NOT NULL,\n            `order_status` VARCHAR(255) NOT NULL DEFAULT 'pending',\n            `tracking_number` VARCHAR(50) DEFAULT NULL,\n            `cod_amount` DECIMAL(10, 2) DEFAULT NULL,\n            `package_weight` DECIMAL(10, 2) DEFAULT NULL,\n            `order_date` DATE DEFAULT NULL,\n            `delivery_date` DATE DEFAULT NULL,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            FOREIGN KEY (orderable_item_id) REFERENCES orderable_item(id)\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error (StudentOrder): " . $e->getMessage());
        }
    }

    public function read()
    {
        $query = 'SELECT so.*, oi.name as item_name, oi.price, oi.course_id, oi.course_bucket_id FROM ' . $this->table . ' so LEFT JOIN orderable_item oi ON so.orderable_item_id = oi.id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single($id)
    {
        $query = 'SELECT so.*, oi.name as item_name, oi.price, oi.course_id, oi.course_bucket_id FROM ' . $this->table . ' so LEFT JOIN orderable_item oi ON so.orderable_item_id = oi.id WHERE so.id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFiltered($filters)
    {
        $query = 'SELECT so.*, oi.name as item_name, oi.price, oi.course_id, oi.course_bucket_id FROM ' . $this->table . ' so 
                  LEFT JOIN orderable_item oi ON so.orderable_item_id = oi.id WHERE 1=1';

        if (!empty($filters['course_id'])) {
            $query .= ' AND oi.course_id = :course_id';
        }
        if (!empty($filters['course_bucket_id'])) {
            $query .= ' AND oi.course_bucket_id = :course_bucket_id';
        }
        if (!empty($filters['status'])) {
            $query .= ' AND so.order_status = :status';
        }
        if (!empty($filters['student_number'])) {
            $query .= ' AND so.student_number = :student_number';
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($filters['course_id'])) {
            $stmt->bindParam(':course_id', $filters['course_id']);
        }
        if (!empty($filters['course_bucket_id'])) {
            $stmt->bindParam(':course_bucket_id', $filters['course_bucket_id']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['student_number'])) {
            $stmt->bindParam(':student_number', $filters['student_number']);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getLatestByStudentNumber($student_number)
    {
        $query = 'SELECT so.*, oi.name as item_name, oi.price, oi.course_id, oi.course_bucket_id FROM ' . $this->table . ' so 
                  LEFT JOIN orderable_item oi ON so.orderable_item_id = oi.id 
                  WHERE so.student_number = :student_number 
                  ORDER BY so.created_at DESC 
                  LIMIT 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_number, orderable_item_id, order_status, tracking_number, cod_amount, package_weight, order_date, delivery_date) VALUES (:student_number, :orderable_item_id, :order_status, :tracking_number, :cod_amount, :package_weight, :order_date, :delivery_date)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':order_status', $data['order_status']);
        $stmt->bindParam(':tracking_number', $data['tracking_number']);
        $stmt->bindParam(':cod_amount', $data['cod_amount']);
        $stmt->bindParam(':package_weight', $data['package_weight']);
        $stmt->bindParam(':order_date', $data['order_date']);
        $stmt->bindParam(':delivery_date', $data['delivery_date']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed_fields = ['student_number', 'orderable_item_id', 'order_status', 'tracking_number', 'cod_amount', 'package_weight', 'order_date', 'delivery_date'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($fields)) {
            return false; // No valid fields to update
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute($params);
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
