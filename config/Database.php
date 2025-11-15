<?php

require_once __DIR__ . '/../models/User.php';

class Database
{
    private $host = 'localhost';
    private $db_name = 'lms';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Automatically create the users table if it doesn't exist
            User::createTable($this->conn);

        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}

// Initialize the database and get the connection
$database = new Database();
$GLOBALS['pdo'] = $database->connect();
