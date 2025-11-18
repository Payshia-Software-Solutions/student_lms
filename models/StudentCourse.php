<?php

class StudentCourse
{
    private $conn;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS student_course (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            student_number VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT NULL,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
            FOREIGN KEY (student_number) REFERENCES users(student_number) ON DELETE CASCADE
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new student course entry
    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO student_course (course_id, student_number, created_by) VALUES (:course_id, :student_number, :created_by)");

        $stmt->execute([
            ':course_id' => $data['course_id'],
            ':student_number' => $data['student_number'],
            ':created_by' => $GLOBALS['jwtPayload']->data->id ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    // Get all student course entries
    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM student_course");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single student course entry by ID
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM student_course WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all course entries for a student
    public function getByStudentNumber($studentNumber)
    {
        $stmt = $this->conn->prepare("SELECT * FROM student_course WHERE student_number = ?");
        $stmt->execute([$studentNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update a student course entry
    public function update($id, $data)
    {
        $fields = [];
        if (isset($data['course_id'])) $fields['course_id'] = $data['course_id'];
        if (isset($data['student_number'])) $fields['student_number'] = $data['student_number'];
        $fields['updated_by'] = $GLOBALS['jwtPayload']->data->id ?? null;

        if (count($fields) <= 1) {
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

        $stmt = $this->conn->prepare("UPDATE student_course SET $setClause WHERE id = :id");

        return $stmt->execute($fields);
    }

    // Delete a student course entry
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM student_course WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
