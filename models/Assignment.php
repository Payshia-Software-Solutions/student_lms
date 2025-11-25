<?php

class Assignment
{
    private $pdo;

    // Table name
    private $table_name = "assigment";

    // Object Properties
    public $id;
    public $course_id;
    public $course_bucket_id;
    public $content_type;
    public $content_title;
    public $content;
    public $view_count;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

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
                    view_count INT DEFAULT 0,
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

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO " . $this->table_name . " (course_id, course_bucket_id, content_type, content_title, content, created_by, updated_by)
            VALUES (:course_id, :course_bucket_id, :content_type, :content_title, :content, :created_by, :updated_by)
        ");

        $stmt->execute([
            ':course_id' => $data['course_id'],
            ':course_bucket_id' => $data['course_bucket_id'],
            ':content_type' => $data['content_type'],
            ':content_title' => $data['content_title'],
            ':content' => $data['content'],
            ':created_by' => $data['created_by'] ?? null,
            ':updated_by' => $data['updated_by'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $key !== 'id') {
                $fields[$key] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields['id'] = $id;
        $setClause = "";
        foreach ($fields as $key => $value) {
            if ($key !== 'id') {
                $setClause .= "$key = :$key, ";
            }
        }
        $setClause = rtrim($setClause, ', ');

        $stmt = $this->pdo->prepare("UPDATE " . $this->table_name . " SET $setClause WHERE id = :id");

        return $stmt->execute($fields);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
