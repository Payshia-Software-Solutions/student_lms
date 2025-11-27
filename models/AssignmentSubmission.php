<?php
class AssignmentSubmission
{
    private $pdo;
    private $table_name = "assigment_submition";

    public $id;
    public $student_number;
    public $course_bucket_id;
    public $assigment_id;
    public $file_path;
    public $grade;
    public $sub_count;
    public $sub_status;
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
        // ... (omitted for brevity)
    }

    public function findByStudentAndAssignment($student_number, $assigment_id)
    {
        // ... (omitted for brevity)
    }

    public function getAll()
    {
        // ... (omitted for brevity)
    }

    public function getById($id)
    {
        // ... (omitted for brevity)
    }

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

        if (!empty($filters['assigment_id'])) {
            $where_clauses[] = "asub.assigment_id = :assigment_id";
            $params[':assigment_id'] = $filters['assigment_id'];
        }

        if (!empty($where_clauses)) {
             $query .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        // ... (omitted for brevity)
    }

    public function update($id, $data)
    {
        // ... (omitted for brevity)
    }

    public function patch($id, $data)
    {
        // ... (omitted for brevity)
    }

    public function delete($id)
    {
        // ... (omitted for brevity)
    }
}
