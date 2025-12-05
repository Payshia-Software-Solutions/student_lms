<?php
    include_once __DIR__ . '/../config/Database.php';
    include_once __DIR__ . '/../models/User.php';
    include_once __DIR__ . '/../models/StudentCourse.php';
    include_once __DIR__ . '/../models/Course.php';
    include_once __DIR__ . '/../models/CourseBucket.php';
    include_once __DIR__ . '/../models/CourseBucketContent.php';
    include_once __DIR__ . '/../models/Assignment.php';
    include_once __DIR__ . '/../models/Enrollment.php';

    class UserFullDetailsController
    {
        private $db;
        private $user;
        private $studentCourse;
        private $course;
        private $courseBucket;
        private $courseBucketContent;
        private $assignment;
        private $enrollment;

        public function __construct()
        {
            $database = new Database();
            $this->db = $database->connect();
            $this->user = new User($this->db);
            $this->studentCourse = new StudentCourse($this->db);
            $this->course = new Course($this->db);
            $this->courseBucket = new CourseBucket($this->db);
            $this->courseBucketContent = new CourseBucketContent($this->db);
            $this->assignment = new Assignment($this->db);
            $this->enrollment = new Enrollment($this->db);
        }

        public function getUserWithCourseDetails()
        {
            if (isset($_GET['student_number'])) {
                $student_number = $_GET['student_number'];
                $user = $this->user->getByStudentNumber($student_number);

                if ($user) {
                    $enrollments = $this->enrollment->getByStudentAndStatus($student_number, 'approved');
                    $coursesWithDetails = [];
                    
                    $enrollment_num = $enrollments->rowCount();

                    if ($enrollment_num > 0) {
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
                                    'buckets' => []
                                ];

                                $buckets = $this->courseBucket->getByCourseId($course_id);
                                while ($bucket_row = $buckets->fetch(PDO::FETCH_ASSOC)) {
                                    $bucketDetails = $bucket_row;
                                    $contents = $this->courseBucketContent->getByBucketId($bucket_row['id']);
                                    $bucketDetails['contents'] = $contents->fetchAll(PDO::FETCH_ASSOC);
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
    }
?>