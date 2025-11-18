<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/StudentCourse.php';
require_once __DIR__ . '/../utils/JwtHelper.php';

class UserController
{
    private $user;
    private $company;
    private $studentCourse;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
        $this->company = new Company($pdo);
        $this->studentCourse = new StudentCourse($pdo);
    }

    public function getAllRecords()
    {
        $users = $this->user->getAll();
        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    public function getRecordById($id)
    {
        $user = $this->user->getById($id);
        if ($user) {
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    public function getRecordByStudentNumber($studentNumber)
    {
        $user = $this->user->getByStudentNumber($studentNumber);
        if ($user) {
            $courses = $this->studentCourse->getByStudentNumber($studentNumber);
            $user['courses'] = $courses;
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Generate student number for 'student' or 'admin' user_status
        if (isset($data['user_status']) && ($data['user_status'] === 'student' || $data['user_status'] === 'admin')) {
            if (isset($data['company_id'])) {
                $trifix = $this->company->getTrifixById($data['company_id']);
                if ($trifix) {
                    $lastId = $this->user->getLastStudentId();
                    $nextId = $lastId + 1;
                    $paddedId = str_pad($nextId, 5, '0', STR_PAD_LEFT);
                    $data['student_number'] = $trifix . '-' . $paddedId;
                } else {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid company_id']);
                    return;
                }
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'company_id is required for students and admins']);
                return;
            }
        } else {
            $data['student_number'] = null;
        }

        $newId = $this->user->create($data);
        if ($newId) {
            $user = $this->user->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'User created successfully', 'data' => $user]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create user. A user with this email or NIC may already exist.']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($this->user->update($id, $data)) {
            $user = $this->user->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully', 'data' => $user]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update user']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->user->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully (soft delete)']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete user']);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['identifier']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Identifier and password are required']);
            return;
        }
        
        $user = $this->user->login($data['identifier'], $data['password']);
        
        if ($user) {
            $token = JwtHelper::generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'f_name' => $user['f_name'],
                'l_name' => $user['l_name']
            ]);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid identifier or password']);
        }
    }
}
