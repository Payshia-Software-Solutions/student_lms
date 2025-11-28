<?php

class CourseBucket
{
    private $conn;

    // Properties
    public $id;
    public $course_id;
    public $name;
    public $description;
    public $payment_type;
    public $payment_amount;
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
        // ... (code is unchanged) ...
    }

    // Create a new course bucket
    public function create($data)
    {
        // ... (code is unchanged) ...
    }

    // Get all course buckets
    public function getAll()
    {
        // ... (code is unchanged) ...
    }

    // Get a single course bucket by ID
    public function getById($id)
    {
        // ... (code is unchanged) ...
    }

    public function getByFilters($filters)
    {
        $query = "SELECT * FROM course_bucket WHERE 1=1";
        $params = [];

        // A whitelist of allowed filter columns to prevent SQL injection
        $allowed_filters = ['id', 'course_id', 'name', 'payment_type', 'is_active'];

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (in_array($key, $allowed_filters)) {
                    $query .= " AND `" . $key . "` = :" . $key;
                    $params[':' . $key] = $value;
                }
            }
        }

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error (getByFilters): " . $e->getMessage());
            return [];
        }
    }

    // Get all course buckets for a specific course
    public function getByCourseId($course_id)
    {
        // ... (code is unchanged) ...
    }

    // Update a course bucket
    public function update($id, $data)
    {
        // ... (code is unchanged) ...
    }

    // Delete a course bucket
    public function delete($id)
    {
        // ... (code is unchanged) ...
    }
}
