<?php

require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/CourseBucket.php';
require_once __DIR__ . '/../models/CourseBucketContent.php';

class CourseController
{
    private $db;
    private $course;
    private $courseBucket;
    private $courseBucketContent;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->course = new Course($pdo);
        $this->courseBucket = new CourseBucket($pdo);
        $this->courseBucketContent = new CourseBucketContent($pdo);
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->course->create($data);
        if ($newId) {
            $createdCourse = $this->course->getById($newId);
            if ($createdCourse) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Course was created.",
                    "data" => $createdCourse
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to retrieve created course."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create course."));
        }
    }

    public function getAllRecords()
    {
        $courses = $this->course->getAll();
        if (!empty($courses)) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $courses]);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No courses found."));
        }
    }

    public function getRecordById($id)
    {
        $course_item = $this->course->getById($id);
        if ($course_item) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $course_item]);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Course not found."));
        }
    }
    
    public function getCourseWithDetails()
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Course ID parameter is required.']);
            return;
        }

        $course = $this->course->getById($id);
        if (!$course) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Course not found.']);
            return;
        }

        $courseBuckets = $this->courseBucket->getByFilters(['course_id' => $id]);
        
        $buckets_with_content = [];
        if (!empty($courseBuckets)) {
            foreach ($courseBuckets as $bucket) {
                $bucketContents = $this->courseBucketContent->getByFilters(['course_bucket_id' => $bucket['id']]);
                $bucket['content'] = !empty($bucketContents) ? $bucketContents : [];
                $buckets_with_content[] = $bucket;
            }
        }

        $course['buckets'] = $buckets_with_content;

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $course]);
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->course->update($id, $data)) {
            $updatedCourse = $this->course->getById($id);
            if ($updatedCourse) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Course was updated.",
                    "data" => $updatedCourse
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Course not found after update."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update course."));
        }
    }

    public function deleteRecord($id)
    {
        if ($this->course->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Course was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete course."));
        }
    }

    public function createCourseTable()
    {
        // Note: This is for setup and should be used with caution
        Course::createTable($this->db);
        echo "Course table created successfully.";
    }
}
