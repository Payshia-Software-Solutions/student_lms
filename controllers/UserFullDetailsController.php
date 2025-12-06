<?php
    include_once __DIR__ . '/../config/Database.php';
    include_once __DIR__ . '/../config/ftp.php';
    include_once __DIR__ . '/../models/User.php';
    include_once __DIR__ . '/../models/UserFullDetails.php';
    include_once __DIR__ . '/../models/Course.php';
    include_once __DIR__ . '/../models/CourseBucket.php';
    include_once __DIR__ . '/../models/CourseBucketContent.php';
    include_once __DIR__ . '/../models/Enrollment.php';
    include_once __DIR__ . '/../controllers/AssignmentController.php';

    class UserFullDetailsController
    {
        private $db;
        private $user;
        private $userFullDetails;
        private $course;
        private $courseBucket;
        private $courseBucketContent;
        private $enrollment;
        private $assignmentController;

        public function __construct($pdo)
        {
            global $ftp_config;

            $this->db = $pdo;
            $this->user = new User($this->db);
            $this->userFullDetails = new UserFullDetails($this->db);
            $this->course = new Course($this->db);
            $this->courseBucket = new CourseBucket($this->db);
            $this->courseBucketContent = new CourseBucketContent($this->db);
            $this->enrollment = new Enrollment($this->db);
            $this->assignmentController = new AssignmentController($this->db, $ftp_config);
        }

        public function getUserWithCourseDetails()
        {
            if (isset($_GET['student_number'])) {
                $student_number = $_GET['student_number'];
                $user = $this->user->getByStudentNumber($student_number);

                if ($user) {
                    $enrollments = $this->enrollment->getByStudentAndStatus($student_number, 'approved');
                    $coursesWithDetails = [];
                    
                    if ($enrollments->rowCount() > 0) {
                        while ($enrollment_row = $enrollments->fetch(PDO::FETCH_ASSOC)) {
                            $course_id = $enrollment_row['course_id'];
                            $course_data = $this->course->getById($course_id);

                            if ($course_data) {
                                $courseDetails = [
                                    'id' => $course_data['id'],
                                    'course_name' => $course_data['course_name'],
                                    'course_description' => $course_data['description'],
                                    'course_image' => $course_data['img_url'],
                                    'created_at' => $course_data['created_at'],
                                    'buckets' => [],
                                    'assignments' => []
                                ];

                                $buckets = $this->courseBucket->getByCourseId($course_id);
                                while ($bucket_row = $buckets->fetch(PDO::FETCH_ASSOC)) {
                                    // CORRECT: Only add the bucket details, not the content within it.
                                    $courseDetails['buckets'][] = $bucket_row;
                                }
                                
                                $courseDetails['assignments'] = $this->assignmentController->fetchAssignmentsAndSubmissionsForStudent($course_id, $student_number);

                                $coursesWithDetails[] = $courseDetails;
                            }
                        }
                    }

                    $user['courses'] = $coursesWithDetails;
                    echo json_encode(['status' => 'success', 'data' => $user]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            }
        }

        // --- OTHER FUNCTIONS ---

        public function getAllRecords()
        {
            $stmt = $this->userFullDetails->read();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        }

        public function getRecordById($id)
        {
            $user_data = $this->userFullDetails->read_single($id);
            if ($user_data) {
                echo json_encode(['status' => 'success', 'data' => $user_data]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            }
        }

        public function getRecordByStudentNumberQuery()
        {
            if (isset($_GET['student_number'])) {
                $student_number = $_GET['student_number'];
                $user_data = $this->userFullDetails->read_by_student_number($student_number);
                if ($user_data) {
                    echo json_encode(['status' => 'success', 'data' => $user_data]);
                } else {
                    http_response_code(200);
                    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                }
            } else {
                 http_response_code(400);
                 echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            }
        }

        public function createRecord()
        {
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data['student_number']) || empty($data['full_name'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields: student_number and full_name are required.']);
                return;
            }

            if ($this->userFullDetails->create($data)) {
                echo json_encode(['status' => 'success', 'message' => 'User details created.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User details could not be created.']);
            }
        }

        public function updateRecord($id)
        {
            $data = json_decode(file_get_contents("php://input"), true);
            if ($this->userFullDetails->update($id, $data)) {
                echo json_encode(['status' => 'success', 'message' => 'User details updated.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User details could not be updated.']);
            }
        }

        public function deleteRecord($id)
        {
            if ($this->userFullDetails->delete($id)) {
                 echo json_encode(['status' => 'success', 'message' => 'User details deleted.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User details could not be deleted.']);
            }
        }
        
        public function updateUserAndDetails($student_number)
        {
            $data = json_decode(file_get_contents('php://input'), true);

            // Separate data for each model
            $userData = [];
            $userFullDetailsData = [];

            foreach ($data as $key => $value) {
                if (in_array($key, ['name', 'student_number', 'role', 'status', 'campus_id', 'batch_id'])) {
                    $userData[$key] = $value;
                }
                if (in_array($key, ['civil_status', 'gender', 'address_line_1', 'address_line_2', 'city_id', 'telephone_1', 'telephone_2', 'nic', 'e_mail', 'birth_day', 'updated_by', 'full_name', 'name_with_initials', 'name_on_certificate'])) {
                    $userFullDetailsData[$key] = $value;
                }
            }

            try {
                $this->db->beginTransaction();

                // Update user
                if (!empty($userData)) {
                    $this->user->updateByStudentNumber($student_number, $userData);
                }

                // Update user full details
                if (!empty($userFullDetailsData)) {
                    $this->userFullDetails->updateByStudentNumber($student_number, $userFullDetailsData);
                }

                $this->db->commit();

                echo json_encode(['status' => 'success', 'message' => 'User and details updated successfully.']);
            } catch (Exception $e) {
                $this->db->rollBack();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user and details: ' . $e->getMessage()]);
            }
        }
    }
?>