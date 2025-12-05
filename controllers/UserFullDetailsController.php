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
                    // Use Enrollment model to get approved courses
                    $enrollments = $this->enrollment->getByStudentAndStatus($student_number, 'approved');
                    $coursesWithDetails = [];
                    
                    $enrollment_num = $enrollments->rowCount();

                    if ($enrollment_num > 0) {
                        while ($enrollment_row = $enrollments->fetch(PDO::FETCH_ASSOC)) {
                            $course_id = $enrollment_row['course_id'];
                            $this->course->id = $course_id;
                            $this->course->read_single();

                            if ($this->course->course_name) {
                                $courseDetails = [
                                    'id' => $this->course->id,
                                    'course_name' => $this->course->course_name,
                                    'course_description' => $this->course->course_description,
                                    'course_image' => $this->course->course_image,
                                    'created_at' => $this->course->created_at,
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
?>