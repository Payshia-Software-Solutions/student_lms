<?php

require_once __DIR__ . '/../models/CourseBucket.php';

class CourseBucketController
{
    private $courseBucket;

    public function __construct($pdo)
    {
        $this->courseBucket = new CourseBucket($pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->courseBucket->getAll();
        $courseBuckets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $courseBuckets]);
    }

    public function getRecordById($id)
    {
        if ($this->courseBucket->getById($id)) {
            $courseBucket_item = [
                'id' => $this->courseBucket->id,
                'course_id' => $this->courseBucket->course_id,
                'name' => $this->courseBucket->name,
                'description' => $this->courseBucket->description,
                'payment_type' => $this->courseBucket->payment_type,
                'payment_amount' => $this->courseBucket->payment_amount,
                'is_active' => $this->courseBucket->is_active,
                'created_at' => $this->courseBucket->created_at,
                'created_by' => $this->courseBucket->created_by,
                'updated_at' => $this->courseBucket->updated_at,
                'updated_by' => $this->courseBucket->updated_by,
            ];
            echo json_encode(['status' => 'success', 'data' => $courseBucket_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course bucket not found']);
        }
    }

    public function getRecordsByCourseId($course_id)
    {
        $stmt = $this->courseBucket->getByCourseId($course_id);
        $courseBuckets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $courseBuckets]);
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->courseBucket->create($data);
        if ($newId) {
            if ($this->courseBucket->getById($newId)) {
                $courseBucket_item = [
                    'id' => $this->courseBucket->id,
                    'course_id' => $this->courseBucket->course_id,
                    'name' => $this->courseBucket->name,
                    'description' => $this->courseBucket->description,
                    'payment_type' => $this->courseBucket->payment_type,
                    'payment_amount' => $this->courseBucket->payment_amount,
                    'is_active' => $this->courseBucket->is_active,
                    'created_at' => $this->courseBucket->created_at,
                    'created_by' => $this->courseBucket->created_by,
                    'updated_at' => $this->courseBucket->updated_at,
                    'updated_by' => $this->courseBucket->updated_by,
                ];
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Course bucket created successfully', 'data' => $courseBucket_item]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to retrieve created course bucket.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create course bucket']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->courseBucket->update($id, $data)) {
            if ($this->courseBucket->getById($id)) {
                $courseBucket_item = [
                    'id' => $this->courseBucket->id,
                    'course_id' => $this->courseBucket->course_id,
                    'name' => $this->courseBucket->name,
                    'description' => $this->courseBucket->description,
                    'payment_type' => $this->courseBucket->payment_type,
                    'payment_amount' => $this->courseBucket->payment_amount,
                    'is_active' => $this->courseBucket->is_active,
                    'created_at' => $this->courseBucket->created_at,
                    'created_by' => $this->courseBucket->created_by,
                    'updated_at' => $this->courseBucket->updated_at,
                    'updated_by' => $this->courseBucket->updated_by,
                ];
                echo json_encode(['status' => 'success', 'message' => 'Course bucket updated successfully', 'data' => $courseBucket_item]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Course bucket not found after update']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update course bucket']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->courseBucket->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Course bucket deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete course bucket']);
        }
    }
}
