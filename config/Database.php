<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Province.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/Student.php';

class Database
{
    // private $host = 'localhost';
    // private $db_name = 'student_lms';
    // private $username = 'root';
    // private $password = '';
    // private $conn;


    private $host = '91.204.209.19';
    private $db_name = 'payshiac_student_lms';
    private $username = 'payshiac_student_lms';
    private $password = '[]M.ujKl{b-a{ASr';
    private $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // One-time setup logic can be placed here, controlled by a flag or configuration.
            // For example, you could check if a certain table exists before running setup.

        } catch (PDOException $e) {
            // Use error_log for production environments
            error_log('Connection Error: ' . $e->getMessage());
            // Optionally, you can have a more user-friendly error message.
            // die("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }
}

// Initialize the database and get the connection
$database = new Database();
$GLOBALS['pdo'] = $database->connect();
