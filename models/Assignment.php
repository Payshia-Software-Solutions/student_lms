<?php

class Assignment
{
    private $pdo;
    private $table_name = "assigment";

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS assigment (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    course_id INT NOT NULL,
                    course_bucket_id INT NOT NULL,
                    content_type VARCHAR(50) NOT NULL,
                    content_title VARCHAR(255) NOT NULL,
                    content TEXT NOT NULL,
                    file_url VARCHAR(255) NULL, -- Added file_url
                    view_count INT DEFAULT 0,
                    submition_count INT DEFAULT 3, -- Added submition_count
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    created_by INT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT
                )
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCourseId($course_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE course_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get assignments by course_id and course_bucket_id
    public function getByCourseAndBucket($course_id, $course_bucket_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE course_id = ? AND course_bucket_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$course_id, $course_bucket_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO " . $this->table_name . " (course_id, course_bucket_id, content_type, content_title, content, file_url, submition_count, created_by, updated_by)
            VALUES (:course_id, :course_bucket_id, :content_type, :content_title, :content, :file_url, :submition_count, :created_by, :updated_by)
        ");

        // Bind parameters
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':content_title', $data['content_title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':file_url', $data['file_url']);
        $stmt->bindParam(':submition_count', $data['submition_count']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $fields = [
            'course_id', 'course_bucket_id', 'content_type', 'content_title',
            'content', 'file_url', 'submition_count', 'updated_by'
        ];
        $query_parts = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $query_parts[] = "$field = :$field";
            }
        }
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $query_parts) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
