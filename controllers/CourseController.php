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
    private $ftp_config;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->course = new Course($pdo);
        $this->courseBucket = new CourseBucket($pdo);
        $this->courseBucketContent = new CourseBucketContent($pdo);

        // Load FTP configuration
        $this->ftp_config = require __DIR__ . '/../config/ftp.php';
    }

    private function send_via_ftp($file)
    {
        $ftp_server = $this->ftp_config['server'];
        $ftp_user_name = $this->ftp_config['username'];
        $ftp_user_pass = $this->ftp_config['password'];
        $remote_path = '/uploads/' . basename($file['name']);

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) {
            error_log("FTP connection failed: $ftp_server");
            return false;
        }

        if (!ftp_login($conn_id, $ftp_user_name, $ftp_user_pass)) {
            error_log("FTP login failed for user $ftp_user_name");
            ftp_close($conn_id);
            return false;
        }

        if (ftp_put($conn_id, $remote_path, $file['tmp_name'], FTP_BINARY)) {
            ftp_close($conn_id);
            return $remote_path;
        } else {
            error_log("FTP upload failed for file: " . $file['name']);
            ftp_close($conn_id);
            return false;
        }
    }

    public function createRecord()
    {
        $data = $_POST;
        if (isset($_FILES['img_url'])) {
            $img_path = $this->send_via_ftp($_FILES['img_url']);
            if ($img_path) {
                $data['img_url'] = $img_path;
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload image."]);
                return;
            }
        }

        $newId = $this->course->create($data);
        if ($newId) {
            $createdCourse = $this->course->getById($newId);
            http_response_code(201);
            echo json_encode(["message" => "Course was created.", "data" => $createdCourse]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to create course."]);
        }
    }

    public function updateRecord($id)
    {
        $data = $_POST;
        if (isset($_FILES['img_url'])) {
            $img_path = $this->send_via_ftp($_FILES['img_url']);
            if ($img_path) {
                $data['img_url'] = $img_path;
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to upload image."]);
                return;
            }
        }

        if ($this->course->update($id, $data)) {
            $updatedCourse = $this->course->getById($id);
            http_response_code(200);
            echo json_encode(["message" => "Course was updated.", "data" => $updatedCourse]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Unable to update course."]);
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
        Course::createTable($this->db);
        echo "Course table created successfully.";
    }
}
