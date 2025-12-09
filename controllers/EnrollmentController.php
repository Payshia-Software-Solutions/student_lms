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
            $filters = [];
            $allowed_filters = ['student_id', 'course_id', 'status', 'student_number'];

            foreach ($allowed_filters as $filter) {
                if (isset($_GET[$filter])) {
                    $filters[$filter] = $_GET[$filter];
                }
            }

            if (isset($filters['student_number']) && !isset($filters['student_id'])) {
                $filters['student_id'] = $filters['student_number'];
                unset($filters['student_number']);
            }

            if (!empty($filters)) {
                $stmt = $this->enrollment->getByFilters($filters);
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
                echo json_encode(array());
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
            $data = json_decode(file_get_contents("php://input"), true);
            $this->enrollment->id = $id;

            if ($this->enrollment->update($data)) {
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