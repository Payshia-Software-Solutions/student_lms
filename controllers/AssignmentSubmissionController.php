<?php
    include_once __DIR__ . '/../config/Database.php';
    include_once __DIR__ . '/../models/AssignmentSubmission.php';

    class AssignmentSubmissionController
    {
        private $db;
        private $assignmentSubmission;

        public function __construct($pdo)
        {
            $this->db = $pdo;
            $this->assignmentSubmission = new AssignmentSubmission($this->db);
        }

        public function getAllRecords()
        {
            $submissions = $this->assignmentSubmission->getAll();
            echo json_encode(['status' => 'success', 'data' => $submissions]);
        }

        public function getRecordById($id)
        {
            $submission = $this->assignmentSubmission->getById($id);
            if ($submission) {
                echo json_encode(['status' => 'success', 'data' => $submission]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found']);
            }
        }

        public function getRecordsByFilter()
        {
            if (!isset($_GET['course_id']) || !isset($_GET['course_bucket_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'course_id and course_bucket_id are required.']);
                return;
            }

            $filters = [
                'course_id' => filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT),
                'course_bucket_id' => filter_input(INPUT_GET, 'course_bucket_id', FILTER_SANITIZE_NUMBER_INT),
                'student_number' => filter_input(INPUT_GET, 'student_number', FILTER_SANITIZE_STRING),
                'assigment_id' => filter_input(INPUT_GET, 'assigment_id', FILTER_SANITIZE_NUMBER_INT),
                'sub_status' => filter_input(INPUT_GET, 'sub_status', FILTER_SANITIZE_STRING)
            ];

            $filters = array_filter($filters);

            $submissions = $this->assignmentSubmission->getByFilters($filters);
            echo json_encode(['status' => 'success', 'data' => $submissions]);
        }

        public function createRecord()
        {
            $data = json_decode(file_get_contents("php://input"), true);
            $newId = $this->assignmentSubmission->create($data);

            if ($newId) {
                $newSubmission = $this->assignmentSubmission->getById($newId);
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Assignment submission created successfully', 'data' => $newSubmission]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment submission']);
            }
        }

        public function updateRecord($id)
        {
            $data = json_decode(file_get_contents("php://input"), true);
            $this->assignmentSubmission->update($id, $data);
            $updatedSubmission = $this->assignmentSubmission->getById($id);

            if ($updatedSubmission) {
                echo json_encode(['status' => 'success', 'message' => 'Assignment submission updated successfully', 'data' => $updatedSubmission]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Assignment submission not found']);
            }
        }

        public function deleteRecord($id)
        {
            if ($this->assignmentSubmission->delete($id)) {
                echo json_encode(['status' => 'success', 'message' => 'Assignment submission deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment submission']);
            }
        }
    }
?>