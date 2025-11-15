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

            // Drop tables in reverse order of creation
            Student::dropTable($this->conn);
            City::dropTable($this->conn);
            District::dropTable($this->conn);
            Province::dropTable($this->conn);

            // Create tables
            User::createTable($this->conn);
            Province::createTable($this->conn);
            District::createTable($this->conn);
            City::createTable($this->conn);
            Student::createTable($this->conn);

            // Seed tables
            Province::seed($this->conn);
            District::seed($this->conn);
            City::seed($this->conn);

        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}

// Initialize the database and get the connection
$database = new Database();
$GLOBALS['pdo'] = $database->connect();
