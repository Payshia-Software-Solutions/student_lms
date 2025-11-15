<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Get all users
    public function getAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE is_active = 1
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user by ID
    public function getById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new user
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users
            (f_name, l_name, email, password, nic, is_active, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['f_name'],
            $data['l_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['nic'],
            $data['is_active'] ?? 1,
            $data['created_by'] ?? null
        ]);
    }

    // Update a user
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET f_name = ?, l_name = ?, email = ?, nic = ?, updated_at = NOW(), created_by = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['f_name'],
            $data['l_name'],
            $data['email'],
            $data['nic'],
            $data['created_by'] ?? null,
            $id
        ]);
    }

    // Soft delete user (set inactive)
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET is_active = 0, updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }
}
