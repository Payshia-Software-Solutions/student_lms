<?php

require_once __DIR__ . '/../models/Enrollment.php';

class EnrollmentController
{
    private $db;
    private $enrollment;

    public function __construct($db)
    {
        $this->db = $db;
        $this->enrollment = new Enrollment($this->db);
    }

    public function getEnrollments()
    {
        if (isset($_GET['student_id']) && isset($_GET['course_id'])) {
            $this->getRecordsByStudentAndCourse($_GET['student_id'], $_GET['course_id']);
        } else {
            $this->getAllRecords();
        }
    }
    
    public function getEnrollmentsByCourse($course_id)
    {
        $stmt = $this->enrollment->getByCourseIdWithCourseName($course_id);
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($enrollments) {
            $this->successResponse($enrollments);
        } else {
            $this->errorResponse("No enrollments found for this course.");
        }
    }

    public function getEnrollmentsByStatus($status)
    {
        $include = isset($_GET['include']) ? $_GET['include'] : '';

        if ($include === 'student') {
            $stmt = $this->enrollment->getStudentsByEnrollmentStatus($status);
        } else {
            $stmt = $this->enrollment->getByStatus($status);
        }

        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($enrollments) {
            $this->successResponse($enrollments);
        } else {
            $this->errorResponse("No enrollments found with that status.");
        }
    }

    public function getApprovedEnrollmentsForStudent($student_id)
    {
        $stmt = $this->enrollment->getApprovedByStudent($student_id);
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($enrollments) {
            $this->successResponse($enrollments);
        } else {
            $this->errorResponse("No approved enrollments found for this student.");
        }
    }

    private function getAllRecords()
    {
        $stmt = $this->enrollment->read();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->successResponse($enrollments);
    }

    private function getEnrollmentData($id)
    {
        $this->enrollment->id = $id;
        if ($this->enrollment->read_single()) {
            return [
                'id' => (int)$this->enrollment->id,
                'student_id' => (string)$this->enrollment->student_id,
                'course_id' => (int)$this->enrollment->course_id,
                'enrollment_date' => $this->enrollment->enrollment_date,
                'grade' => $this->enrollment->grade,
                'status' => $this->enrollment->status
            ];
        }
        return null;
    }

    public function getRecordById($id)
    {
        $record = $this->getEnrollmentData($id);
        if ($record) {
            $this->successResponse($record);
        } else {
            $this->errorResponse("Enrollment not found.");
        }
    }

    public function getEnrollmentsByStudent($student_id)
    {
        $stmt = $this->enrollment->getByStudentId($student_id);
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($enrollments) {
            $this->successResponse($enrollments);
        } else {
            $this->errorResponse("No enrollments found for this student.");
        }
    }

    private function getRecordsByStudentAndCourse($student_id, $course_id)
    {
        $stmt = $this->enrollment->read_by_student_and_course($student_id, $course_id);
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($enrollments) {
            $this->successResponse($enrollments);
        } else {
            $this->errorResponse("No enrollments found for this student and course.");
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->student_id) || !isset($data->course_id)) {
            $this->errorResponse("Missing student_id or course_id in request body");
            return;
        }

        $result = $this->enrollment->create($data);

        if ($result === 'exists') {
            $this->errorResponse("This student is already enrolled in this course.", 409);
        } else if (is_numeric($result)) {
            $record = $this->getEnrollmentData($result);
            $this->successResponse([
                'message' => 'Enrollment created successfully.',
                'data' => $record
            ], 201);
        } else {
            $this->errorResponse("Failed to create enrollment.");
        }
    }

    public function deleteRecord($id)
    {
        $this->enrollment->id = $id;
        if ($this->enrollment->delete()) {
            $this->successResponse(["message" => "Enrollment deleted successfully."]);
        } else {
            $this->errorResponse("Failed to delete enrollment.");
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"));

        if (empty(get_object_vars($data))) {
            $this->errorResponse("No data provided for update.");
            return;
        }

        if ($this->enrollment->update($id, $data)) {
            $record = $this->getEnrollmentData($id);
            $this->successResponse([
                'message' => 'Enrollment updated successfully.',
                'data' => $record
            ]);
        } else {
            $this->errorResponse("Failed to update enrollment.");
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
