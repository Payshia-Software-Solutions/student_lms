<?php

require_once __DIR__ . '/../models/UserFullDetails.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/StudentCourse.php';
require_once __DIR__ . '/../controllers/CourseController.php';

class UserFullDetailsController
{
    private $pdo;
    private $userFullDetails;
    private $user;
    private $studentCourse;
    private $courseController;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userFullDetails = new UserFullDetails($this->pdo);
        $this->user = new User($this->pdo);
        $this->studentCourse = new StudentCourse($this->pdo);
        $this->courseController = new CourseController($this->pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->userFullDetails->read();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($records);
    }

    public function getRecordById($id)
    {
        $record = $this->userFullDetails->read_single($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse("Record not found.", 404);
        }
    }
    
    public function getRecordByStudentNumber($student_number)
    {
        $record = $this->userFullDetails->read_by_student_number($student_number);
        if ($record) {
            $this->successResponse(['found' => true, 'data' => $record]);
        } else {
            $this->successResponse(['found' => false, 'data' => null]);
        }
    }

    public function getRecordByStudentNumberQuery()
    {
        if (isset($_GET['student_number'])) {
            $student_number = $_GET['student_number'];
            $user = $this->user->getByStudentNumber($student_number);
            $userDetails = $this->userFullDetails->read_by_student_number($student_number);

            if ($user || $userDetails) {
                $this->successResponse(['found' => true, 'data' => array_merge((array)$user, (array)$userDetails)]);
            } else {
                $this->successResponse(['found' => false, 'data' => null]);
            }
        } else {
            $this->errorResponse("Student number is required.", 400);
        }
    }

    public function getUserWithCourseDetails()
    {
        if (isset($_GET['student_number'])) {
            $student_number = $_GET['student_number'];
            $user = $this->user->getByStudentNumber($student_number);
            $studentCourses = $this->studentCourse->getByStudentNumber($student_number);

            if ($user) {
                $courses = [];
                foreach ($studentCourses as $studentCourse) {
                    // This is not ideal, but we have to work with the tools we have
                    $_GET['id'] = $studentCourse['course_id'];
                    $courseDetails = $this->courseController->getCourseWithDetails();
                    $courses[] = json_decode($courseDetails, true)['data'];
                }
                $user['courses'] = $courses;
                $this->successResponse(['found' => true, 'data' => $user]);
            } else {
                $this->successResponse(['found' => false, 'data' => null]);
            }
        } else {
            $this->errorResponse("Student number is required.", 400);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $this->userFullDetails->create($data);
        if ($id) {
            $this->successResponse(['id' => $id, 'message' => 'Record created successfully.'], 201);
        } else {
            $this->errorResponse("Failed to create record.", 500);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->userFullDetails->update($id, $data)) {
            $this->successResponse(['id' => $id, 'message' => 'Record updated successfully.']);
        } else {
            $this->errorResponse("Failed to update record.", 500);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->userFullDetails->delete($id)) {
            $this->successResponse(['id' => $id, 'message' => 'Record deleted successfully.']);
        } else {
            $this->errorResponse("Failed to delete record.", 500);
        }
    }

    private function successResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function errorResponse($message, $statusCode = 400)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['message' => $message]);
    }
}
