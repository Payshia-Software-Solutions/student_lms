<?php
    include_once __DIR__ . '/../config/Database.php';
    include_once __DIR__ . '/../models/User.php';
    include_once __DIR__ . '/../models/Course.php';
    include_once __DIR__ . '/../models/CourseBucket.php';
    include_once __DIR__ . '/../models/CourseBucketContent.php';
    include_once __DIR__ . '/../models/Assignment.php';
    include_once __DIR__ . '/../models/Enrollment.php';

    class UserFullDetailsController
    {
        private $db;
        private $user;
        private $course;
        private $courseBucket;
        private $courseBucketContent;
        private $assignment;
        private $enrollment;

        public function __construct($pdo)
        {
            $this->db = $pdo;
            $this->user = new User($this->db);
            $this->course = new Course($this->db);
            $this->courseBucket = new CourseBucket($this->db);
            $this->courseBucketContent = new CourseBucketContent($this->db);
            $this->assignment = new Assignment($this->db);
            $this->enrollment = new Enrollment($this->db);
        }

        // Get all user records
        public function getAllRecords()
        {
            $stmt = $this->user->read();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        }

        // Get a single user by ID
        public function getRecordById($id)
        {
            $this->user->id = $id;
            if ($this->user->read_single()) {
                $user_data = [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                    'student_number' => $this->user->student_number
                ];
                echo json_encode(['status' => 'success', 'data' => $user_data]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            }
        }

        // Get user by student number from query param
        public function getRecordByStudentNumberQuery()
        {
            if (isset($_GET['student_number'])) {
                $student_number = $_GET['student_number'];
                $user_data = $this->user->getByStudentNumber($student_number);
                if ($user_data) {
                    echo json_encode(['status' => 'success', 'data' => $user_data]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                }
            } else {
                 http_response_code(400);
                 echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            }
        }

        // The corrected function we worked on
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
                                    $bucketDetails = $bucket_row;
                                    $contents = $this->courseBucketContent->getByCourseBucketId($bucket_row['id']);
                                    $bucketDetails['contents'] = $contents;
                                    $courseDetails['buckets'][] = $bucketDetails;
                                }
                                
                                $assignments = $this->assignment->getByCourseId($course_id);
                                $courseDetails['assignments'] = $assignments->fetchAll(PDO::FETCH_ASSOC);

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

        // Create a new user record
        public function createRecord()
        {
            $data = json_decode(file_get_contents("php://input"));
            // Basic validation
            if (empty($data->username) || empty($data->email) || empty($data->password)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
                return;
            }

            if ($this->user->create($data)) {
                echo json_encode(['status' => 'success', 'message' => 'User created.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User could not be created.']);
            }
        }

        // Update a user record
        public function updateRecord($id)
        {
            $data = json_decode(file_get_contents("php://input"));
            if ($this->user->update($id, $data)) {
                echo json_encode(['status' => 'success', 'message' => 'User updated.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User could not be updated.']);
            }
        }

        // Delete a user record
        public function deleteRecord($id)
        {
            if ($this->user->delete($id)) {
                 echo json_encode(['status' => 'success', 'message' => 'User deleted.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'User could not be deleted.']);
            }
        }
    }
?>