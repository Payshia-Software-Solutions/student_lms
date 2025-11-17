<?php

require_once __DIR__ . '/../models/Course.php';

class CourseController
{
    private $course;
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->course = new Course($pdo);
    }

    public function createCourse()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->course->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Course was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create course."));
        }
    }

    public function getCourses()
    {
        $stmt = $this->course->getAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $courses_arr = array();
            $courses_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $course_item = array(
                    "id" => $id,
                    "course_name" => $course_name,
                    "course_code" => $course_code,
                    "description" => $description,
                    "credits" => $credits,
                    "payment_status" => $payment_status
                );
                array_push($courses_arr["records"], $course_item);
            }

            http_response_code(200);
            echo json_encode($courses_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No courses found."));
        }
    }

    public function getCourse($id)
    {
        if ($this->course->getById($id)) {
            $course_item = array(
                "id" => $this->course->id,
                "course_name" => $this->course->course_name,
                "course_code" => $this->course->course_code,
                "description" => $this->course->description,
                "credits" => $this->course->credits,
                "payment_status" => $this->course->payment_status
            );
            http_response_code(200);
            echo json_encode($course_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Course not found."));
        }
    }

    public function updateCourse($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['id'] = $id;

        if ($this->course->update($data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Course was updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update course."));
        }
    }

    public function deleteCourse($id)
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
