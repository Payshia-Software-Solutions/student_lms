<?php

require_once __DIR__ . '/../models/CourseBucketContent.php';

class CourseBucketContentController
{
    private $courseBucketContent;

    public function __construct($pdo)
    {
        $this->courseBucketContent = new CourseBucketContent($pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->courseBucketContent->getAll();
        $courseBucketContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $courseBucketContents]);
    }

    public function getRecordById($id)
    {
        if ($this->courseBucketContent->getById($id)) {
            $courseBucketContent_item = [
                'id' => $this->courseBucketContent->id,
                'course_id' => $this->courseBucketContent->course_id,
                'course_bucket_id' => $this->courseBucketContent->course_bucket_id,
                'content_type' => $this->courseBucketContent->content_type,
                'content_title' => $this->courseBucketContent->content_title,
                'content' => $this->courseBucketContent->content,
                'view_count' => $this->courseBucketContent->view_count,
                'is_active' => $this->courseBucketContent->is_active,
                'created_at' => $this->courseBucketContent->created_at,
                'created_by' => $this->courseBucketContent->created_by,
                'updated_at' => $this->courseBucketContent->updated_at,
                'updated_by' => $this->courseBucketContent->updated_by,
            ];
            echo json_encode(['status' => 'success', 'data' => $courseBucketContent_item]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course bucket content not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newId = $this->courseBucketContent->create($data);
        if ($newId) {
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Course bucket content created successfully', 'data' => ['id' => $newId]]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create course bucket content']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->courseBucketContent->update($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Course bucket content updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update course bucket content']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->courseBucketContent->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Course bucket content deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete course bucket content']);
        }
    }
}
