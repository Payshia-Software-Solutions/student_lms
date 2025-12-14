<?php

class Bank
{
    private $conn;
    private $table = 'bank';

    public $id;
    public $name;
    public $bank_code;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS bank (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    bank_code VARCHAR(50) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by VARCHAR(100) NOT NULL,
                    updated_by VARCHAR(100) NOT NULL
                );
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating bank table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = 'INSERT INTO ' . $this->table . ' (name, bank_code, created_by, updated_by) VALUES (:name, :bank_code, :created_by, :updated_by)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':bank_code', $data['bank_code']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET name = :name, bank_code = :bank_code, updated_by = :updated_by WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':bank_code', $data['bank_code']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>