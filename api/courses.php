<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/CourseController.php';

header("Content-Type: application/json");

$database = new Database();
$db = $database->connect();

$controller = new CourseController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? explode('/', rtrim($_GET['path'], '/')) : [];

if ($method == 'POST' && empty($path)) {
    $controller->createCourse();
} elseif ($method == 'GET' && empty($path)) {
    $controller->getCourses();
} elseif ($method == 'GET' && count($path) == 1 && $path[0] == 'create_table') {
    $controller->createCourseTable();
} elseif ($method == 'GET' && count($path) == 1 && is_numeric($path[0])) {
    $controller->getCourse($path[0]);
} elseif ($method == 'PUT' && count($path) == 1 && is_numeric($path[0])) {
    $controller->updateCourse($path[0]);
} elseif ($method == 'DELETE' && count($path) == 1 && is_numeric($path[0])) {
    $controller->deleteCourse($path[0]);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Not Found"));
}
