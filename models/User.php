<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public static function createTable($pdo)
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    f_name VARCHAR(50) NOT NULL,
                    l_name VARCHAR(50) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    nic VARCHAR(20) UNIQUE,
                    is_active BOOLEAN DEFAULT 1,
                    created_by INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT id, f_name, l_name, email, nic, is_active, created_at, updated_at FROM users WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT id, f_name, l_name, email, nic, is_active, created_at, updated_at FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("\n            INSERT INTO users (f_name, l_name, email, password, nic, created_by)\n            VALUES (:f_name, :l_name, :email, :password, :nic, :created_by)\n        ");

        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        
        return $stmt->execute([
            ':f_name' => $data['f_name'],
            ':l_name' => $data['l_name'],
            ':email' => $data['email'],
            ':password' => $password,
            ':nic' => $data['nic'] ?? null,
            ':created_by' => $GLOBALS['jwtPayload']->data->id ?? null // Assuming creator is the logged-in user
        ]);
    }

    public function update($id, $data)
    {
        $fields = [];
        if (isset($data['f_name'])) $fields['f_name'] = $data['f_name'];
        if (isset($data['l_name'])) $fields['l_name'] = $data['l_name'];
        if (isset($data['email'])) $fields['email'] = $data['email'];
        if (isset($data['nic'])) $fields['nic'] = $data['nic'];
        if (isset($data['password'])) $fields['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        if (empty($fields)) {
            return false;
        }

        $fields['id'] = $id;

        $setClause = "";
        foreach ($fields as $key => $value) {
            if ($key !== 'id') {
                $setClause .= "$key = :$key, ";
            }
        }
        $setClause = rtrim($setClause, ', ');

        $stmt = $this->pdo->prepare("UPDATE users SET $setClause WHERE id = :id");

        return $stmt->execute($fields);
    }
    
    public function delete($id)
    {
        // Soft delete
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
