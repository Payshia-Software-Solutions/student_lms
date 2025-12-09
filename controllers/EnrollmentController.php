<?php
    include_once __DIR__ . '/../config/Database.php';
    include_once __DIR__ . '/../models/Enrollment.php';

    class EnrollmentController
    {
        private $db;
        private $enrollment;

        public function __construct()
        {
            $database = new Database();
            $this->db = $database->connect();
            $this->enrollment = new Enrollment($this->db);
        }

        public function getAllRecords()
        {
            $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;
            $student_number = isset($_GET['student_number']) ? $_GET['student_number'] : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;

            if ($student_number && $status) {
                $stmt = $this->enrollment->getByStudentAndStatus($student_number, $status);
            } elseif ($student_id) {
                $stmt = $this->enrollment->getByStudentId($student_id);
            } else {
                $stmt = $this->enrollment->read();
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $enrollments_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $enrollment_item = array(
                        'id' => $id,
                        'student_id' => $student_id,
                        'course_id' => $course_id,
                        'enrollment_date' => $enrollment_date,
                        'grade' => $grade,
                        'status' => $status
                    );
                    array_push($enrollments_arr, $enrollment_item);
                }
                echo json_encode($enrollments_arr);
            } else {
                echo json_encode(array('message' => 'No enrollments found.'));
            }
        }

        public function getRecordById($id)
        {
            $this->enrollment->id = $id;
            $found = $this->enrollment->read_single();

            if ($found) {
                $enrollment_item = array(
                    'id' => $this->enrollment->id,
                    'student_id' => $this->enrollment->student_id,
                    'course_id' => $this->enrollment->course_id,
                    'enrollment_date' => $this->enrollment->enrollment_date,
                    'grade' => $this->enrollment->grade,
                    'status' => $this->enrollment->status
                );
                echo json_encode($enrollment_item);
            } else {
                http_response_code(404);
                echo json_encode(array('message' => 'Enrollment not found.'));
            }
        }

        public function createRecord()
        {
            $data = json_decode(file_get_contents("php://input"));

            $this->enrollment->student_id = $data->student_id;
            $this->enrollment->course_id = $data->course_id;
            $this->enrollment->status = 'pending';

            if ($this->enrollment->create()) {
                echo json_encode(array('id' => $this->enrollment->id, 'message' => 'Enrollment created.'));
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Enrollment not created.'));
            }
        }

        public function updateRecord($id)
        {
            $data = json_decode(file_get_contents("php://input"));

            $this->enrollment->id = $id;
            $this->enrollment->student_id = $data->student_id;
            $this->enrollment->course_id = $data->course_id;
            $this->enrollment->enrollment_date = $data->enrollment_date;
            $this->enrollment->grade = $data->grade;
            $this->enrollment->status = $data->status;

            if ($this->enrollment->update()) {
                echo json_encode(array('message' => 'Enrollment updated.'));
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Enrollment not updated.'));
            }
        }

        public function deleteRecord($id)
        {
            $this->enrollment->id = $id;

            if ($this->enrollment->delete()) {
                echo json_encode(array('message' => 'Enrollment deleted.'));
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Enrollment not deleted.'));
            }
        }
    }
?>