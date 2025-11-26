<?php

class CourseBucketContent
{
    private $conn;

    // Properties
    public $id;
    public $course_id;
    public $course_bucket_id;
    public $content_type;
    public $content_title;
    public $content;
    public $view_count;
    public $is_active;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create table
    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS course_bucket_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id INT NOT NULL,
            course_bucket_id INT NOT NULL,
            content_type ENUM('video', 'pdf', 'text', 'image', 'link') NOT NULL,
            content_title VARCHAR(255) NOT NULL,
            content LONGTEXT,
            view_count INT DEFAULT 0,
            is_active BOOLEAN NOT NULL DEFAULT true,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT,
            FOREIGN KEY (course_id) REFERENCES courses(id),
            FOREIGN KEY (course_bucket_id) REFERENCES course_bucket(id)
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    // Create a new course bucket content
    public function create($data)
    {
        $query = "INSERT INTO course_bucket_content (course_id, course_bucket_id, content_type, content_title, content, is_active, created_by, updated_by) VALUES (:course_id, :course_bucket_id, :content_type, :content_title, :content, :is_active, :created_by, :updated_by)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':content_title', $data['content_title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get all course bucket contents
    public function getAll()
    {
        $query = "SELECT * FROM course_bucket_content";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get a single course bucket content by ID
    public function getById($id)
    {
        $query = "SELECT * FROM course_bucket_content WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->course_id = $row['course_id'];
            $this->course_bucket_id = $row['course_bucket_id'];
            $this->content_type = $row['content_type'];
            $this->content_title = $row['content_title'];
            $this->content = $row['content'];
            $this->view_count = $row['view_count'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->created_by = $row['created_by'];
            $this->updated_at = $row['updated_at'];
            $this->updated_by = $row['updated_by'];
            return true;
        }
        return false;
    }
    
    // **NEW**: Get all content for a specific course bucket
    public function getByCourseBucketId($course_bucket_id)
    {
        $query = "SELECT * FROM course_bucket_content WHERE course_bucket_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_bucket_id);
        $stmt->execute();
        return $stmt;
    }

    // Update a course bucket content
    public function update($id, $data)
    {
        $query = "UPDATE course_bucket_content SET course_id = :course_id, course_bucket_id = :course_bucket_id, content_type = :content_type, content_title = :content_title, content = :content, is_active = :is_active, updated_by = :updated_by WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':course_id', $data['course_id']);
        $stmt->bindParam(':course_bucket_id', $data['course_bucket_id']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':content_title', $data['content_title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':updated_by', $data['updated_by']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a course bucket content
    public function delete($id)
    {
        $query = "DELETE FROM course_bucket_content WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
