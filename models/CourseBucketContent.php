<?php

class CourseBucketContent
{
    private $conn;
    private $table_name = 'course_bucket_content';

    public $id;
    public $course_id;
    public $course_bucket_id;
    public $content_type;
    public $content_title;
    public $content;
    public $view_count;
    public $is_active;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS course_bucket_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT,
            course_bucket_id INT,
            content_type VARCHAR(255),
            content_title VARCHAR(255),
            content TEXT,
            view_count INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT
        );";
        $pdo->exec($sql);
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByFilters($filters)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];

        $allowed_filters = ['id', 'course_id', 'course_bucket_id', 'content_type', 'is_active'];

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $allowed_filters)) {
                    $query .= " AND `" . $key . "` = :" . $key;
                    $params[':' . $key] = $value;
                }
            }
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error (getByFilters CourseBucketContent): " . $e->getMessage());
            return [];
        }
    }

    public function getByCourseBucketId($bucket_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE course_bucket_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $bucket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCourseAndBucket($course_id, $bucket_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE course_id = ? AND course_bucket_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_id);
        $stmt->bindParam(2, $bucket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM course_bucket_content WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->course_bucket_id = $row['course_bucket_id'];
            $this->content_type = $row['content_type'];
            $this->content_title = $row['content_title'];
            $this->content = $row['content'];
            $this->view_count = $row['view_count'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            $this->updated_at = $row['updated_at'];
            $this->updated_by = $row['updated_by'];
            return $row;
        }

        return false;
    }

    public function create($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (course_id, course_bucket_id, content_type, content_title, content, created_by, updated_by) VALUES (:course_id, :course_bucket_id, :content_type, :content_title, :content, :created_by, :updated_by)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':content_title', $data['content_title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed_fields = ['content_type', 'content_title', 'content', 'is_active', 'updated_by'];

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "`$field` = :$field";
                $params[':' . $field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return true; // No fields to update
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($params)) {
            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
