<?php

require_once 'config/database.php';
require_once 'controllers/UserController.php';
require_once 'controllers/CourseController.php';
require_once 'controllers/StudentCourseController.php';

// Get the database connection
$database = new Database();
$pdo = $database->getConnection();

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'];

// A simple router
switch ($request_uri) {
    // User routes
    case '/users':
        $controller = new UserController($pdo);
        $controller->getAllRecords();
        break;
    case (preg_match('/\/users\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new UserController($pdo);
        $controller->getRecordById($matches[1]);
        break;
    case '/users/create':
        $controller = new UserController($pdo);
        $controller->createRecord();
        break;
    case (preg_match('/\/users\/update\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new UserController($pdo);
        $controller->updateRecord($matches[1]);
        break;
    case (preg_match('/\/users\/delete\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new UserController($pdo);
        $controller->deleteRecord($matches[1]);
        break;
    case '/login':
        $controller = new UserController($pdo);
        $controller->login();
        break;

    // Course routes
    case '/courses':
        $controller = new CourseController($pdo);
        $controller->getAllRecords();
        break;
    case (preg_match('/\/courses\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new CourseController($pdo);
        $controller->getRecordById($matches[1]);
        break;
    case '/courses/create':
        $controller = new CourseController($pdo);
        $controller->createRecord();
        break;
    case (preg_match('/\/courses\/update\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new CourseController($pdo);
        $controller->updateRecord($matches[1]);
        break;
    case (preg_match('/\/courses\/delete\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new CourseController($pdo);
        $controller->deleteRecord($matches[1]);
        break;
    case '/courses/create-table':
        $controller = new CourseController($pdo);
        $controller->createCourseTable();
        break;

    // Student Course routes
    case '/student-courses':
        $controller = new StudentCourseController($pdo);
        $controller->getAllRecords();
        break;
    case (preg_match('/\/student-courses\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new StudentCourseController($pdo);
        $controller->getRecordById($matches[1]);
        break;
    case '/student-courses/create':
        $controller = new StudentCourseController($pdo);
        $controller->createRecord();
        break;
    case (preg_match('/\/student-courses\/update\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new StudentCourseController($pdo);
        $controller->updateRecord($matches[1]);
        break;
    case (preg_match('/\/student-courses\/delete\/(\d+)'/, $request_uri, $matches) ? true : false):
        $controller = new StudentCourseController($pdo);
        $controller->deleteRecord($matches[1]);
        break;
    case '/student-courses/create-table':
        $controller = new StudentCourseController($pdo);
        $controller->createStudentCourseTable();
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Route not found."));
        break;
}
