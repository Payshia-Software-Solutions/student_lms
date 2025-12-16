<?php

class UserFullDetails
{
    private $conn;
    private $table = 'user_full_details';

    // ... (existing properties)

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ... (existing methods like createTable, read, read_single, etc.)

    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function read_single($id)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function read_by_student_number($student_number)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE student_number = :student_number';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        // ... implementation ...
    }

    public function update($id, $data)
    {
        // ... implementation ...
    }
    
    public function updateByStudentNumber($studentNumber, $data)
    {
        // ... implementation ...
    }

    public function delete($id)
    {
        // ... implementation ...
    }

    // --- NEWLY ADDED FUNCTION ---
    public function getStudentCoursesWithDetails($student_number)
    {
        $query = "
            SELECT 
                c.id as course_id, c.course_name, c.description as course_description, c.image as course_image,
                cb.id as course_bucket_id, cb.course_bucket_name, cb.course_bucket_price,
                cc.id as course_content_id, cc.name as course_content_name, cc.type as course_content_type, cc.is_free,
                scp.id as progress_id, scp.status as progress_status
            FROM 
                student_course sc
            JOIN 
                course c ON sc.course_id = c.id
            LEFT JOIN 
                course_bucket cb ON cb.course_id = c.id
            LEFT JOIN 
                course_content cc ON cc.course_bucket_id = cb.id
            LEFT JOIN 
                student_content_progress scp ON scp.course_content_id = cc.id AND scp.student_number = :student_number
            WHERE 
                sc.student_number = :student_number
            ORDER BY
                c.id, cb.id, cc.id;
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $courses = [];

        foreach ($results as $row) {
            if (!$row['course_id']) continue;

            if (!isset($courses[$row['course_id']])) {
                $courses[$row['course_id']] = [
                    'id' => $row['course_id'],
                    'course_name' => $row['course_name'],
                    'description' => $row['course_description'],
                    'image' => $row['course_image'],
                    'course_buckets' => []
                ];
            }

            if ($row['course_bucket_id'] && !isset($courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']])) {
                $courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']] = [
                    'id' => $row['course_bucket_id'],
                    'course_bucket_name' => $row['course_bucket_name'],
                    'course_bucket_price' => $row['course_bucket_price'],
                    'course_contents' => []
                ];
            }

            if ($row['course_content_id']) {
                $courses[$row['course_id']]['course_buckets'][$row['course_bucket_id']]['course_contents'][] = [
                    'id' => $row['course_content_id'],
                    'course_content_name' => $row['course_content_name'],
                    'course_content_type' => $row['course_content_type'],
                    'is_free' => $row['is_free'],
                    'progress' => $row['progress_id'] ? [
                        'id' => $row['progress_id'],
                        'status' => $row['progress_status']
                    ] : null
                ];
            }
        }

        return array_values(array_map(function($course) {
            if (isset($course['course_buckets'])) {
                $course['course_buckets'] = array_values($course['course_buckets']);
            }
            return $course;
        }, $courses));
    }
}
