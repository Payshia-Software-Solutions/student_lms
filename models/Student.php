<?php

class Student
{
    private $conn;
    private $table = 'students';

    // Student Properties
    public $id;
    public $username;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $parent_name;
    public $phone_number;
    public $parent_phone_number;
    public $address;
    public $city_id;
    public $nic;
    public $profile_image_url;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $table = 'students';
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            username VARCHAR(255) NOT NULL,\n            first_name VARCHAR(255) NOT NULL,\n            last_name VARCHAR(255) NOT NULL,\n            date_of_birth DATE,\n            gender ENUM('Male', 'Female', 'Other'),\n            parent_name VARCHAR(255),\n            phone_number VARCHAR(20) NOT NULL,\n            parent_phone_number VARCHAR(20),\n            address TEXT,\n            city_id INT,\n            nic VARCHAR(20),\n            profile_image_url VARCHAR(255),\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL,\n            UNIQUE KEY (username),\n            FOREIGN KEY (city_id) REFERENCES cities(id)\n        )";

        try {
            $db->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log('Connection Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function dropTable($db)
    {
        $query = "DROP TABLE IF EXISTS students";

        try {
            $db->exec($query);
        } catch (PDOException $e) {
            error_log("Error dropping table 'students': " . $e->getMessage());
        }
    }
}
