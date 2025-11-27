<?php
class AssignmentSubmission
{
    private $pdo;
    private $table_name = "assigment_submition";

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $query = "INSERT INTO " . $this->table_name . " (student_number, course_bucket_id, assigment_id, file_path, sub_count, sub_status, created_by, updated_by) VALUES (:student_number, :course_bucket_id, :assigment_id, :file_path, :sub_count, :sub_status, :created_by, :updated_by)";
        $stmt = $this->pdo->prepare($query);

        // Bind parameters
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':assigment_id', $data['assigment_id']);
        $stmt->bindParam(':file_path', $data['file_path']);
        $stmt->bindParam(':sub_count', $data['sub_count']);
        $stmt->bindParam(':sub_status', $data['sub_status']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error on create for assigment_submition: " . $errorInfo[2]);
            return false;
        }
    }

    public function update($id, $data)
    {
        // This is a full update, so we expect all fields.
        $query = "UPDATE " . $this->table_name . " SET student_number = :student_number, course_bucket_id = :course_bucket_id, assigment_id = :assigment_id, file_path = :file_path, grade = :grade, sub_count = :sub_count, sub_status = :sub_status, updated_by = :updated_by, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($query);

        $params = array_merge($data, ['id' => $id]);

        if ($stmt->execute($params)) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error on update for assigment_submition ID {$id}: " . $errorInfo[2]);
            return false;
        }
    }

    public function patch($id, $data)
    {
        $set_clauses = [];
        $params = ['id' => $id];
        $allowed_columns = ['file_path', 'grade', 'sub_count', 'sub_status', 'updated_by'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_columns)) {
                $set_clauses[] = "`$key` = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($set_clauses)) {
            error_log("Patch failed: No valid fields provided for update on ID {$id}");
            return false;
        }

        $set_clauses[] = "updated_at = CURRENT_TIMESTAMP";

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clauses) . " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($query);

        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("PDOException on patch for assigment_submition ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error on delete for assigment_submition ID {$id}: " . $errorInfo[2]);
            return false;
        }
    }
}
