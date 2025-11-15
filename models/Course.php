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
        $query = "CREATE TABLE IF NOT EXISTS courses (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            course_name VARCHAR(255) NOT NULL,\n            description TEXT,\n            credits INT NOT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }
}
