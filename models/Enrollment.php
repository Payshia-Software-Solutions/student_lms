<?php

class Enrollment
{
    private $conn;
    private $table = 'enrollments';

    public $id;
    public $student_id;
    public $course_id;
    public $enrollment_date;
    public $grade;
    public $status;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS `enrollments` (\n            `id` int(11) NOT NULL AUTO_INCREMENT,\n            `student_id` varchar(55) NOT NULL,\n            `course_id` int(11) NOT NULL,\n            `enrollment_date` date DEFAULT NULL,\n            `grade` varchar(2) DEFAULT NULL,\n            `created_at` timestamp NULL DEFAULT current_timestamp(),\n            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),\n            `deleted_at` timestamp NULL DEFAULT NULL,\n            `status` enum('pending','rejected','approved') DEFAULT 'pending',\n            PRIMARY KEY (`id`),\n            KEY `student_id` (`student_id`),\n            KEY `course_id` (`course_id`),\n            CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getByCourseIdWithCourseName($course_id)
    {
        $query = 'SELECT e.*, c.course_name FROM ' . $this->table . ' e JOIN courses c ON e.course_id = c.id WHERE e.course_id = :course_id AND e.deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);

        $course_id = htmlspecialchars(strip_tags($course_id));
        $stmt->bindParam(':course_id', $course_id);

        $stmt->execute();
        return $stmt;
    }

    public function getByStatus($status)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE status = :status AND deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);

        $status = htmlspecialchars(strip_tags($status));
        $stmt->bindParam(':status', $status);

        $stmt->execute();
        return $stmt;
    }

    public function getStudentsByEnrollmentStatus($status)
    {
        $query = 'SELECT \n                    u.id as student_user_id, \n                    u.f_name, \n                    u.l_name, \n                    u.email, \n                    u.student_number, \n                    e.id as enrollment_id, \n                    e.status as enrollment_status, \n                    e.enrollment_date, \n                    c.id as course_id, \n                    c.course_name \n                  FROM \n                    ' . $this->table . ' e \n                  JOIN \n                    users u ON e.student_id = u.student_number \n                  JOIN \n                    courses c ON e.course_id = c.id \n                  WHERE \n                    e.status = :status AND e.deleted_at IS NULL';

        $stmt = $this->conn->prepare($query);

        $status = htmlspecialchars(strip_tags($status));
        $stmt->bindParam(':status', $status);

        $stmt->execute();
        return $stmt;
    }

    public function getApprovedByStudent($student_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE student_id = :student_id AND status = \'approved\' AND deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);

        $student_id = htmlspecialchars(strip_tags($student_id));
        $stmt->bindParam(':student_id', $student_id);

        $stmt->execute();
        return $stmt;
    }

    public function read_single()
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->student_id = $row['student_id'];
            $this->course_id = $row['course_id'];
            $this->enrollment_date = $row['enrollment_date'];
            $this->grade = $row['grade'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function read_by_student_and_course($student_id, $course_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE student_id = :student_id AND course_id = :course_id AND deleted_at IS NULL';
        $stmt = $this->conn->prepare($query);

        $student_id = htmlspecialchars(strip_tags($student_id));
        $course_id = htmlspecialchars(strip_tags($course_id));

        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        return $stmt;
    }

    public function create($data)
    {
        $check_query = 'SELECT id FROM ' . $this->table . ' WHERE student_id = :student_id AND course_id = :course_id AND deleted_at IS NULL';
        $check_stmt = $this->conn->prepare($check_query);

        $student_id = htmlspecialchars(strip_tags($data->student_id));
        $course_id = htmlspecialchars(strip_tags($data->course_id));

        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->bindParam(':course_id', $course_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return 'exists';
        }

        $fields = get_object_vars($data);

        if (!isset($fields['status'])) {
            $fields['status'] = 'pending';
        }

        $allowed_columns = ['student_id', 'course_id', 'enrollment_date', 'grade', 'status'];
        $columns = [];
        $placeholders = [];
        $values_to_bind = [];

        foreach ($fields as $key => $value) {
            if (in_array($key, $allowed_columns)) {
                $columns[] = "`$key`";
                $placeholders[] = ":$key";
                $values_to_bind[$key] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($columns)) {
            return false;
        }

        $query = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $this->conn->prepare($query);

        foreach ($values_to_bind as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        error_log("DB Create Error: " . implode(", ", $stmt->errorInfo()));
        return false;
    }

    public function update($id, $data)
    {
        $fields = get_object_vars($data);

        $allowed_columns = ['student_id', 'course_id', 'enrollment_date', 'grade', 'status'];
        $set_clauses = [];
        $values_to_bind = [];

        foreach ($fields as $key => $value) {
            if (in_array($key, $allowed_columns)) {
                $set_clauses[] = "`$key` = :$key";
                $values_to_bind[$key] = htmlspecialchars(strip_tags($value));
            }
        }

        if (empty($set_clauses)) {
            return false;
        }

        $query = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $set_clauses) . ' WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        foreach ($values_to_bind as $key => &$value) {
            $stmt->bindParam(":$key", $value);
        }

        if ($stmt->execute()) {
            return $stmt->rowCount() > 0;
        }

        error_log("DB Update Error: " . implode(", ", $stmt->errorInfo()));
        return false;
    }

    public function delete()
    {
        $query = 'UPDATE ' . $this->table . ' SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
