<?php

class BankBranch
{
    private $conn;
    private $table = 'bank_branch';

    public $id;
    public $bank_id;
    public $branch_name;
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
                CREATE TABLE IF NOT EXISTS bank_branch (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    bank_id INT NOT NULL,
                    branch_name VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_by VARCHAR(100) NOT NULL,
                    updated_by VARCHAR(100) NOT NULL
                );
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating bank_branch table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByBankId($bank_id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE bank_id = :bank_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':bank_id', $bank_id);
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
        $query = 'INSERT INTO ' . $this->table . ' (bank_id, branch_name, created_by, updated_by) VALUES (:bank_id, :branch_name, :created_by, :updated_by)';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':bank_id', $data['bank_id']);
        $stmt->bindParam(':branch_name', $data['branch_name']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = 'UPDATE ' . $this->table . ' SET bank_id = :bank_id, branch_name = :branch_name, updated_by = :updated_by WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':bank_id', $data['bank_id']);
        $stmt->bindParam(':branch_name', $data['branch_name']);
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