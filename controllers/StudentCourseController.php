<?php

require_once __DIR__ . '/../models/StudentCourse.php';

class StudentCourseController
{
    private $studentCourse;
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->studentCourse = new StudentCourse($pdo);
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->studentCourse->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Student course entry was created."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create student course entry."));
        }
    }

    public function getAllRecords()
    {
        $stmt = $this->studentCourse->getAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $studentCourses_arr = array();
            $studentCourses_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $studentCourse_item = array(
                    "id" => $id,
                    "course_id" => $course_id,
                    "student_number" => $student_number,
                    "created_at" => $created_at,
                    "created_by" => $created_by,
                    "updated_at" => $updated_at,
                    "updated_by" => $updated_by
                );
                array_push($studentCourses_arr["records"], $studentCourse_item);
            }

            http_response_code(200);
            echo json_encode($studentCourses_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No student course entries found."));
        }
    }

    public function getRecordById($id)
    {
        if ($this->studentCourse->getById($id)) {
            $studentCourse_item = array(
                "id" => $this->studentCourse->id,
                "course_id" => $this->studentCourse->course_id,
                "student_number" => $this->studentCourse->student_number,
                "created_at" => $this->studentCourse->created_at,
                "created_by" => $this->studentCourse->created_by,
                "updated_at" => $this->studentCourse->updated_at,
                "updated_by" => $this->studentCourse->updated_by
            );
            http_response_code(200);
            echo json_encode($studentCourse_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Student course entry not found."));
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->studentCourse->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Student course entry was updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update student course entry."));
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentCourse->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Student course entry was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete student course entry."));
        }
    }

    public function createStudentCourseTable()
    {
        StudentCourse::createTable($this->db);
        echo "StudentCourse table created successfully.";
    }
}
