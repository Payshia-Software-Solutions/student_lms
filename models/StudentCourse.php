<?php

class StudentCourse
{
    private $conn;

    // Properties
    public $id;
    public $course_id;
    public $student_number;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS student_course (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            student_number VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (student_number) REFERENCES users(student_number) ON DELETE CASCADE
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new student course entry
    public function create($data)
    {
        $query = "INSERT INTO student_course (course_id, student_number, created_by) VALUES (:course_id, :student_number, :created_by)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->course_id = htmlspecialchars(strip_tags($data['course_id']));
        $this->student_number = htmlspecialchars(strip_tags($data['student_number']));
        $this->created_by = isset($data['created_by']) ? htmlspecialchars(strip_tags($data['created_by'])) : null;

        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':student_number', $this->student_number);
        $stmt->bindParam(':created_by', $this->created_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all student course entries
    public function getAll()
    {
        $query = "SELECT * FROM student_course";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single student course entry by ID
    public function getById($id)
    {
        $query = "SELECT * FROM student_course WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->student_number = $row['student_number'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            $this->updated_at = $row['updated_at'];
            $this->updated_by = $row['updated_by'];
            return true;
        }
        return false;
    }

    // Update a student course entry
    public function update($id, $data)
    {
        $query = "UPDATE student_course SET course_id = :course_id, student_number = :student_number, updated_by = :updated_by WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->id = htmlspecialchars(strip_tags($id));
        $this->course_id = htmlspecialchars(strip_tags($data['course_id']));
        $this->student_number = htmlspecialchars(strip_tags($data['student_number']));
        $this->updated_by = isset($data['updated_by']) ? htmlspecialchars(strip_tags($data['updated_by'])) : null;


        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':course_id', $this->course_id);
        $stmt->bindParam(':student_number', $this->student_number);
        $stmt->bindParam(':updated_by', $this->updated_by);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a student course entry
    public function delete($id)
    {
        $query = "DELETE FROM student_course WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
