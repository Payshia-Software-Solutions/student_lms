<?php

class OrderableItem
{
    private $conn;
    private $table = 'orderable_item';

    public $id;
    public $name;
    public $description;
    public $price;
    public $course_id;
    public $course_bucket_id;
    public $img_url;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `orderable_item` (\n            `id` INT AUTO_INCREMENT PRIMARY KEY,\n            `name` VARCHAR(255) NOT NULL,\n            `description` TEXT,\n            `price` DECIMAL(10,2) NOT NULL,\n            `course_id` INT NOT NULL,\n            `course_bucket_id` INT NOT NULL,\n            `img_url` VARCHAR(255) NOT NULL,\n            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            // You might want to log this error instead of echoing
            error_log("Table Creation Error (OrderableItem): " . $e->getMessage());
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
    
    public function readByCourse($course_id, $course_bucket_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE course_id = :course_id AND course_bucket_id = :course_bucket_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':course_bucket_id', $course_bucket_id);
        $stmt->execute();
        return $stmt;
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (name, description, price, course_id, course_bucket_id, img_url) VALUES (:name, :description, :price, :course_id, :course_bucket_id, :img_url)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':img_url', $data['img_url']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET name = :name, description = :description, price = :price, course_id = :course_id, course_bucket_id = :course_bucket_id, img_url = :img_url WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':img_url', $data['img_url']);

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
