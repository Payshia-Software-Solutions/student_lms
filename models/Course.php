<?php

class Course
{
    // Properties
    public $id;
    public $course_name;
    public $description;
    public $credits;
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
        $query = "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_name VARCHAR(255) NOT NULL,
            description TEXT,
            credits INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            // Handle table creation error
            // For now, we'll just print the error
            echo "Table Creation Error: " . $e->getMessage();
        }
    }
}
