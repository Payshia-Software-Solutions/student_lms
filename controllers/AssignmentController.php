<?php

require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../models/AssignmentSubmission.php';

class AssignmentController
{
    private $assignment;
    private $assignmentSubmission;
    private $ftp_config;

    public function __construct($pdo, $ftp_config)
    {
        $this->assignment = new Assignment($pdo);
        $this->assignmentSubmission = new AssignmentSubmission($pdo);
        $this->ftp_config = $ftp_config;
    }

    // NEW, REUSABLE PUBLIC METHOD
    public function fetchAssignmentsAndSubmissionsForStudent($course_id, $student_number)
    {
        $assignments = $this->assignment->getByCourseId($course_id);

        if (!empty($assignments)) {
            foreach ($assignments as &$assignment) {
                $filters = [
                    'assigment_id' => $assignment['id'],
                    'student_number' => $student_number
                ];
                $submissions = $this->assignmentSubmission->getByFilters($filters);
                $assignment['submissions'] = !empty($submissions) ? $submissions : [];
            }
        }
        return $assignments; // Return the data as an array
    }

    // EXISTING ENDPOINT - NOW USES THE NEW METHOD
    public function getAssignmentsForStudentByCourse()
    {
        $course_id = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $student_number = filter_input(INPUT_GET, 'student_number', FILTER_SANITIZE_STRING);

        if (!$course_id || !$student_number) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters: course_id, student_number']);
            return;
        }

        // Call the new reusable method to get the data
        $data = $this->fetchAssignmentsAndSubmissionsForStudent($course_id, $student_number);

        // Echo the final response
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function getAllRecords()
    {
        if (isset($_GET['course_id']) && isset($_GET['course_bucket_id'])) {
            $assignments = $this->assignment->getByCourseAndBucket($_GET['course_id'], $_GET['course_bucket_id']);
        } else {
            $assignments = $this->assignment->getAll();
        }
        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }

    public function getRecordById($id)
    {
        $assignment = $this->assignment->getById($id);
        if ($assignment) {
            echo json_encode(['status' => 'success', 'data' => $assignment]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assignment not found']);
        }
    }

    public function getAssignmentsWithSubmissions()
    {
        $course_id = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT);
        $course_bucket_id = filter_input(INPUT_GET, 'course_bucket_id', FILTER_SANITIZE_NUMBER_INT);
        $student_number = filter_input(INPUT_GET, 'student_number', FILTER_SANITIZE_STRING);

        if (!$course_id || !$course_bucket_id || !$student_number) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters: course_id, course_bucket_id, student_number']);
            return;
        }

        $assignments = $this->assignment->getByCourseAndBucket($course_id, $course_bucket_id);

        if (!empty($assignments)) {
            foreach ($assignments as &$assignment) {
                $filters = [
                    'assigment_id' => $assignment['id'],
                    'student_number' => $student_number
                ];
                $submissions = $this->assignmentSubmission->getByFilters($filters);
                $assignment['submissions'] = !empty($submissions) ? $submissions : [];
            }
        }

        echo json_encode(['status' => 'success', 'data' => $assignments]);
    }

    public function createRecord()
    {
        $data = json_decode($_POST['data'], true);

        if (isset($_FILES['file'])) {
            $file_url = $this->uploadFileViaFTP($_FILES['file']);
            if ($file_url) {
                $data['file_url'] = $file_url;
            } else {
                return;
            }
        }

        $newId = $this->assignment->create($data);
        if ($newId) {
            $newAssignment = $this->assignment->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Assignment created successfully', 'data' => $newAssignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create assignment']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->assignment->update($id, $data)) {
            $updatedAssignment = $this->assignment->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Assignment updated successfully', 'data' => $updatedAssignment]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update assignment']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->assignment->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete assignment']);
        }
    }
    
    private function uploadFileViaFTP($file)
    {
        $ftp_server = $this->ftp_config['server'];
        $ftp_user = $this->ftp_config['user'];
        $ftp_pass = $this->ftp_config['password'];
        $ftp_root = rtrim($this->ftp_config['root_path'], '/');
        $public_url_base = rtrim($this->ftp_config['public_url'], '/');
        $tmp_path = $file['tmp_name'];

        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id || !ftp_login($conn_id, $ftp_user, $ftp_pass)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP connection failed.']);
            return false;
        }
        
        ftp_pasv($conn_id, true);

        $upload_directory_name = 'assignment_files';
        $remote_dir = $ftp_root . '/' . $upload_directory_name;

        if (!@ftp_chdir($conn_id, $remote_dir) && !ftp_mkdir($conn_id, $remote_dir)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create directory on FTP server.']);
            ftp_close($conn_id);
            return false;
        }

        $file_name = uniqid() . '-' . basename($file['name']);
        $remote_path = $remote_dir . '/' . $file_name;

        if (!ftp_put($conn_id, $remote_path, $tmp_path, FTP_BINARY)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'FTP file upload failed.']);
            ftp_close($conn_id);
            return false;
        }

        ftp_close($conn_id);
        return $public_url_base . '/' . $upload_directory_name . '/' . $file_name;
    }
}
