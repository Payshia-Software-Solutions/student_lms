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
            $sql = "\n                CREATE TABLE IF NOT EXISTS users (\n                    id INT AUTO_INCREMENT PRIMARY KEY,\n                    f_name VARCHAR(50) NOT NULL,\n                    l_name VARCHAR(50) NOT NULL,\n                    email VARCHAR(100) UNIQUE NOT NULL,\n                    password VARCHAR(255) NOT NULL,\n                    nic VARCHAR(20) UNIQUE,\n                    phone_number VARCHAR(20) NULL,\n                    user_status ENUM('student', 'admin') NOT NULL DEFAULT 'student',\n                    student_number VARCHAR(20) UNIQUE NULL,\n                    company_id INT,\n                    is_active BOOLEAN DEFAULT 1,\n                    created_by INT,\n                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,\n                    FOREIGN KEY (company_id) REFERENCES company(id) ON DELETE SET NULL\n                )\n            ";
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating table: " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $stmt = $this->pdo->prepare("SELECT id, f_name, l_name, email, nic, phone_number, user_status, student_number, company_id, is_active, created_at, updated_at FROM users WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT id, f_name, l_name, email, nic, phone_number, user_status, student_number, company_id, is_active, created_at, updated_at FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByStudentNumber($studentNumber)
    {
        $stmt = $this->pdo->prepare("SELECT id, f_name, l_name, email, nic, phone_number, user_status, student_number, company_id, is_active, created_at, updated_at FROM users WHERE student_number = ? AND is_active = 1");
        $stmt->execute([$studentNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("\n            INSERT INTO users (f_name, l_name, email, password, nic, phone_number, user_status, student_number, company_id, created_by)\n            VALUES (:f_name, :l_name, :email, :password, :nic, :phone_number, :user_status, :student_number, :company_id, :created_by)\n        ");

        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt->execute([
            ':f_name' => $data['f_name'],
            ':l_name' => $data['l_name'],
            ':email' => $data['email'],
            ':password' => $password,
            ':nic' => $data['nic'] ?? null,
            ':phone_number' => $data['phone_number'] ?? null,
            ':user_status' => $data['user_status'] ?? 'student',
            ':student_number' => $data['student_number'] ?? null,
            ':company_id' => $data['company_id'] ?? null,
            ':created_by' => $GLOBALS['jwtPayload']->data->id ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        if (isset($data['f_name'])) $fields['f_name'] = $data['f_name'];
        if (isset($data['l_name'])) $fields['l_name'] = $data['l_name'];
        if (isset($data['email'])) $fields['email'] = $data['email'];
        if (isset($data['nic'])) $fields['nic'] = $data['nic'];
        if (isset($data['phone_number'])) $fields['phone_number'] = $data['phone_number'];
        if (isset($data['user_status'])) $fields['user_status'] = $data['user_status'];
        if (isset($data['student_number'])) $fields['student_number'] = $data['student_number'];
        if (isset($data['company_id'])) $fields['company_id'] = $data['company_id'];
        if (isset($data['password'])) $fields['password'] = password_hash($data['password'], PASSWORD_BCRYT);

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

    public function findByIdentifier($identifier)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE (email = :identifier OR student_number = :identifier) AND is_active = 1");
        $stmt->execute([':identifier' => $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($identifier, $password)
    {
        $user = $this->findByIdentifier($identifier);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getLastStudentId()
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['id'] : 0;
    }
}
