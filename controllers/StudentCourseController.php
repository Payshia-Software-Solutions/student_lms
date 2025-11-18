<?php

require_once __DIR__ . '/../models/StudentCourse.php';

class StudentCourseController
{
    private $studentCourse;

    public function __construct($pdo)
    {
        $this->studentCourse = new StudentCourse($pdo);
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->studentCourse->create($data);

        if ($newId) {
            $studentCourse = $this->studentCourse->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Student course created successfully', 'data' => $studentCourse]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create student course']);
        }
    }

    public function getAllRecords()
    {
        $studentCourses = $this->studentCourse->getAll();
        echo json_encode(['status' => 'success', 'data' => $studentCourses]);
    }

    public function getRecordById($id)
    {
        $studentCourse = $this->studentCourse->getById($id);
        if ($studentCourse) {
            echo json_encode(['status' => 'success', 'data' => $studentCourse]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student course not found']);
        }
    }

    public function getRecordByStudentNumber($studentNumber)
    {
        $studentCourse = $this->studentCourse->getByStudentNumber($studentNumber);
        if ($studentCourse) {
            echo json_encode(['status' => 'success', 'data' => $studentCourse]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student course not found']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->studentCourse->update($id, $data)) {
            $studentCourse = $this->studentCourse->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Student course updated successfully', 'data' => $studentCourse]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update student course']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->studentCourse->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Student course deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete student course']);
        }
    }
}
