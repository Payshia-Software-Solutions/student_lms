<?php

class Enrollment
{
    // Properties
    public $id;
    public $student_id;
    public $course_id;
    public $enrollment_date;
    public $grade;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS enrollments (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            student_id INT NOT NULL,\n            course_id INT NOT NULL,\n            enrollment_date DATE,\n            grade VARCHAR(2),\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL,\n            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,\n            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }
}
