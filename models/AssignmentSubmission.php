<?php

class AssignmentSubmission
{
    private $pdo;
    private $table_name = "assigment_submition";

    // Object Properties
    public $id;
    public $student_number;
    public $course_bucket_id;
    public $assigment_id;
    public $file_path;
    public $grade;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    public $is_active;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS assigment_submition (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_number VARCHAR(50) NOT NULL,
                    course_bucket_id INT NOT NULL,
                    assigment_id INT NOT NULL,
                    file_path VARCHAR(255),
                    grade VARCHAR(10),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    created_by INT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    updated_by INT,
                    is_active TINYINT(1) DEFAULT 1
                );
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO " . $this->table_name . " (student_number, course_bucket_id, assigment_id, file_path, grade, created_by, updated_by)
            VALUES (:student_number, :course_bucket_id, :assigment_id, :file_path, :grade, :created_by, :updated_by)
        ");

        // Get user ID from the global JWT payload if available
        $userId = $GLOBALS['jwtPayload']->data->id ?? null;

        $stmt->execute([
            ':student_number' => $data['student_number'],
            ':course_bucket_id' => $data['course_bucket_id'],
            ':assigment_id' => $data['assigment_id'],
            ':file_path' => $data['file_path'] ?? null,
            ':grade' => $data['grade'] ?? null,
            ':created_by' => $userId,
            ':updated_by' => $userId
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        // Add current user to updated_by
        $data['updated_by'] = $GLOBALS['jwtPayload']->data->id ?? null;

        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && !in_array($key, ['id', 'created_at', 'created_by'])) {
                $fields[] = "`$key` = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $setClause = implode(', ', $fields);
        $stmt = $this->pdo->prepare("UPDATE " . $this->table_name . " SET $setClause WHERE id = :id");

        return $stmt->execute($params);
    }

    public function delete($id)
    {
        // Soft delete by setting is_active to 0
        $stmt = $this->pdo->prepare("UPDATE " . $this->table_name . " SET is_active = 0, updated_by = ? WHERE id = ?");
        $userId = $GLOBALS['jwtPayload']->data->id ?? null;
        return $stmt->execute([$userId, $id]);
    }
}
