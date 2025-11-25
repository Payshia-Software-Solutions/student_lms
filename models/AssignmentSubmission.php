<?php
class AssignmentSubmission
{
    private $pdo;
    private $table_name = "assignment_submissions";

    public $id;
    public $student_number;
    public $course_bucket_id;
    public $assigment_id;
    public $file_path;
    public $grade;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;
    public $deleted_at; // Corrected property


    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Get all records
    public function getAll()
    {
        // Corrected to use deleted_at
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE deleted_at IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single record by ID
    public function getById($id)
    {
        // Corrected to use deleted_at
        $stmt = $this->pdo->prepare("SELECT * FROM " . $this->table_name . " WHERE id = ? AND deleted_at IS NULL");
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
            WHERE
                asub.deleted_at IS NULL
        "; // Corrected to use deleted_at

        $params = [];

        if (!empty($filters['student_number'])) {
            $query .= " AND asub.student_number = :student_number";
            $params[':student_number'] = $filters['student_number'];
        }

        if (!empty($filters['course_id'])) {
            $query .= " AND cb.course_id = :course_id";
            $params[':course_id'] = $filters['course_id'];
        }

        if (!empty($filters['course_bucket_id'])) {
            $query .= " AND asub.course_bucket_id = :course_bucket_id";
            $params[':course_bucket_id'] = $filters['course_bucket_id'];
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Create a new record
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO " . $this->table_name . " (student_number, course_bucket_id, assigment_id, file_path, grade, created_by, updated_by)
            VALUES (:student_number, :course_bucket_id, :assigment_id, :file_path, :grade, :created_by, :updated_by)
        ");

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


    // Update a record
    public function update($id, $data)
    {
        $data['id'] = $id;
        $userId = $GLOBALS['jwtPayload']->data->id ?? null;
        $data['updated_by'] = $userId;

        $stmt = $this->pdo->prepare("
            UPDATE " . $this->table_name . " SET
                student_number = :student_number,
                course_bucket_id = :course_bucket_id,
                assigment_id = :assigment_id,
                file_path = :file_path,
                grade = :grade,
                updated_by = :updated_by
            WHERE id = :id AND deleted_at IS NULL
        "); // Corrected to use deleted_at

        $stmt->execute([
            ':id' => $data['id'],
            ':student_number' => $data['student_number'],
            ':course_bucket_id' => $data['course_bucket_id'],
            ':assigment_id' => $data['assigment_id'],
            ':file_path' => $data['file_path'] ?? null,
            ':grade' => $data['grade'] ?? null,
            ':updated_by' => $userId
        ]);
        return $stmt->rowCount();
    }


    // Soft delete a record
    public function delete($id)
    {
        // Corrected to use deleted_at
        $stmt = $this->pdo->prepare("UPDATE " . $this->table_name . " SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}
