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

        public static function createTable($pdo)
        {
            try {
                $sql = "\n                    CREATE TABLE IF NOT EXISTS enrollments (\n                        id INT AUTO_INCREMENT PRIMARY KEY,\n                        student_id VARCHAR(255) NOT NULL,\n                        course_id INT NOT NULL,\n                        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n                        grade VARCHAR(10) NULL,\n                        status VARCHAR(50) NOT NULL DEFAULT 'pending',\n                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                        deleted_at TIMESTAMP NULL\n                    )\n                ";
                $pdo->exec($sql);
            } catch (PDOException $e) {
                error_log("Error creating enrollments table: " . $e->getMessage());
            }
        }

        public function read()
        {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE deleted_at IS NULL';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }

        public function read_single()
        {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id AND deleted_at IS NULL';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->student_id = $row['student_id'];
                $this->course_id = $row['course_id'];
                $this->enrollment_date = $row['enrollment_date'];
                $this->grade = $row['grade'];
                $this->status = $row['status'];
                return true;
            }
            return false;
        }

        public function create()
        {
            $query = 'INSERT INTO ' . $this->table . ' (student_id, course_id, status) VALUES (:student_id, :course_id, :status)';
            $stmt = $this->conn->prepare($query);

            $this->student_id = htmlspecialchars(strip_tags($this->student_id));
            $this->course_id = htmlspecialchars(strip_tags($this->course_id));
            $this->status = htmlspecialchars(strip_tags($this->status));

            $stmt->bindParam(':student_id', $this->student_id);
            $stmt->bindParam(':course_id', $this->course_id);
            $stmt->bindParam(':status', $this->status);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        }

        public function update($data)
        {
            $query_parts = [];
            $params = [':id' => $this->id];
            foreach ($data as $key => $value) {
                if (property_exists($this, $key) && $key !== 'id') {
                    $query_parts[] = "`$key` = :$key";
                    $params[":$key"] = htmlspecialchars(strip_tags($value));
                }
            }

            if (empty($query_parts)) {
                return false;
            }

            $query = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $query_parts) . ' WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute($params)) {
                return true;
            }
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

        public function getByFilters($filters)
        {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE 1=1 AND deleted_at IS NULL';
            $params = [];

            if (isset($filters['student_id'])) {
                $query .= " AND student_id = :student_id";
                $params[':student_id'] = $filters['student_id'];
            }
            if (isset($filters['course_id'])) {
                $query .= " AND course_id = :course_id";
                $params[':course_id'] = $filters['course_id'];
            }
            if (isset($filters['status'])) {
                $query .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        }

        public function getByStudentAndStatus($student_id, $status)
        {
            $query = 'SELECT * FROM ' . $this->table . ' WHERE student_id = :student_id AND status = :status AND deleted_at IS NULL';
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            return $stmt;
        }
    }
?>