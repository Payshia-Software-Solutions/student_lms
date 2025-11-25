<?php
class AssignmentSubmission
{
    private $pdo;
    private $table_name = "assigment_submition";

    public $id;
    public $student_number;
    public $course_bucket_id;
    // **FIXED**: Corrected the column name from assigment_id to assignment_id
    public $assignment_id;
    public $file_path;
    public $grade;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    public static function createTable($db)
    {
        // **FIXED**: Corrected the column name in the table definition
        $query = "CREATE TABLE IF NOT EXISTS assigment_submition (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_number VARCHAR(255) NOT NULL,
            course_bucket_id INT NOT NULL,
            assignment_id INT NOT NULL,
            file_path VARCHAR(255),
            grade VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT,
            FOREIGN KEY (course_bucket_id) REFERENCES course_bucket(id)
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error (assigment_submition): " . $e->getMessage());
        }
    }

    // Get all records
    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single record by ID
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get records based on dynamic filters
    public function getByFilters($filters = [])
    {
        $query = "
            SELECT
                asub.*,
                cb.course_id
            FROM
                " . $this->table_name . " asub
            LEFT JOIN
                course_bucket cb ON asub.course_bucket_id = cb.id
        ";

        $params = [];
        $where_clauses = [];

        if (!empty($filters['student_number'])) {
            $where_clauses[] = "asub.student_number = :student_number";
            $params[':student_number'] = $filters['student_number'];
        }

        if (!empty($filters['course_id'])) {
            $where_clauses[] = "cb.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }

        if (!empty($filters['course_bucket_id'])) {
            $where_clauses[] = "asub.course_bucket_id = :course_bucket_id";
            $params[':course_bucket_id'] = $filters['course_bucket_id'];
        }

        if (!empty($where_clauses)) {
             $query .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Create a new record
    public function create($data)
    {
        // **FIXED**: Corrected the column name in the INSERT statement
        $stmt = $this->pdo->prepare("
            INSERT INTO " . $this->table_name . " (student_number, course_bucket_id, assignment_id, file_path, grade, created_by, updated_by)
            VALUES (:student_number, :course_bucket_id, :assignment_id, :file_path, :grade, :created_by, :updated_by)
        ");

        $userId = $GLOBALS['jwtPayload']->data->id ?? null;

        $stmt->execute([
            ':student_number' => $data['student_number'],
            ':course_bucket_id' => $data['course_bucket_id'],
            ':assignment_id' => $data['assignment_id'],
            ':file_path' => $data['file_path'] ?? null,
            ':grade' => $data['grade'] ?? null,
            ':created_by' => $userId,
            ':updated_by' => $userId
        ]);
        return $this->pdo->lastInsertId();
    }


    // Update a record
    public function update($id, $data)
    {
        $data['id'] = $id;
        $userId = $GLOBALS['jwtPayload']->data->id ?? null;
        $data['updated_by'] = $userId;

        // **FIXED**: Corrected the column name in the UPDATE statement
        $stmt = $this->pdo->prepare("
            UPDATE " . $this->table_name . " SET
                student_number = :student_number,
                course_bucket_id = :course_bucket_id,
                assignment_id = :assignment_id,
                file_path = :file_path,
                grade = :grade,
                updated_by = :updated_by
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $data['id'],
            ':student_number' => $data['student_number'],
            ':course_bucket_id' => $data['course_bucket_id'],
            ':assignment_id' => $data['assignment_id'],
            ':file_path' => $data['file_path'] ?? null,
            ':grade' => $data['grade'] ?? null,
            ':updated_by' => $userId
        ]);
        return $stmt->rowCount();
    }


    // Changed to a permanent delete as there is no soft delete column
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
