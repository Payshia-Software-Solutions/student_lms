<?php

class StudentOrder
{
    private $conn;
    private $table = 'student_order';

    public $id;
    public $student_id;
    public $orderable_item_id;
    public $order_status;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `student_order` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `student_id` INT NOT NULL,
            `orderable_item_id` INT NOT NULL,
            `order_status` VARCHAR(255) NOT NULL DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (orderable_item_id) REFERENCES orderable_item(id)
        );";

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

        $stmt->execute();
        return $stmt;
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_id, orderable_item_id, order_status) VALUES (:student_id, :orderable_item_id, :order_status)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':order_status', $data['order_status']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET student_id = :student_id, orderable_item_id = :orderable_item_id, order_status = :order_status WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':student_id', $data['student_id']);
        $stmt->bindParam(':orderable_item_id', $data['orderable_item_id']);
        $stmt->bindParam(':order_status', $data['order_status']);

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
