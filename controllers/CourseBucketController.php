<?php

require_once __DIR__ . '/../models/CourseBucket.php';
require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../models/CourseBucketContent.php';

class CourseBucketController
{
    private $courseBucket;
    private $assignment;
    private $courseBucketContent;

    public function __construct($pdo)
    {
        $this->courseBucket = new CourseBucket($pdo);
        $this->assignment = new Assignment($pdo);
        $this->courseBucketContent = new CourseBucketContent($pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->courseBucket->getAll();
        $courseBuckets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $courseBuckets]);
    }

    public function getRecordsByCourseId($course_id)
    {
        $courseBuckets = $this->courseBucket->getByCourseId($course_id);
        $result = [];

        foreach ($courseBuckets as $bucket) {
            $bucket_id = $bucket['id'];
            
            // Fetch assignments for the current bucket
            $assignments = $this->assignment->getByCourseAndBucket($course_id, $bucket_id);
            $bucket['assignments'] = $assignments;

            // Fetch contents for the current bucket
            $contents = $this->courseBucketContent->getByCourseAndBucket($course_id, $bucket_id);
            $bucket['contents'] = $contents;

            $result[] = $bucket;
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    public function getRecordById($id)
    {
        if ($this->courseBucket->getById($id)) {
            $courseBucket_item = [
                'id' => $this->courseBucket->id,
                'bucket_name' => $this->courseBucket->name,
                'course_id' => $this->courseBucket->course_id,
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

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->courseBucket->create($data);

        if ($newId) {
            if ($this->courseBucket->getById($newId)) {
                $courseBucket_item = [
                    'id' => $this->courseBucket->id,
                    'bucket_name' => $this->courseBucket->name,
                    'course_id' => $this->courseBucket->course_id,
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
                    'bucket_name' => $this->courseBucket->name,
                    'course_id' => $this->courseBucket->course_id,
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
