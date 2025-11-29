
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
        $query = "CREATE TABLE IF NOT EXISTS `enrollments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `student_id` int(11) NOT NULL,
            `course_id` int(11) NOT NULL,
            `enrollment_date` date DEFAULT NULL,
            `grade` varchar(2) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `deleted_at` timestamp NULL DEFAULT NULL,
            `status` enum('pending','rejected','approved') DEFAULT 'pending',
            PRIMARY KEY (`id`),
            KEY `student_id` (`student_id`),
            KEY `course_id` (`course_id`),
            CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
            CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;";

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

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (student_id, course_id, status) VALUES (:student_id, :course_id, :status)';

        $stmt = $this->conn->prepare($query);

        $this->student_id = htmlspecialchars(strip_tags($data->student_id));
        $this->course_id = htmlspecialchars(strip_tags($data->course_id));
        $this->status = $data->status ?? 'pending';

        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        }
        printf("Error: %s.
", $stmt->error);
        return false;
    }

    public function updateStatus($id, $status)
    {
        $query = 'UPDATE ' . $this->table . ' SET status = :status WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($id));
        $this->status = htmlspecialchars(strip_tags($status));

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
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
}
