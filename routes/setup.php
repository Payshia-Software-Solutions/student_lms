<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CourseBucket.php';

$database = new Database();
$pdo = $database->connect();

CourseBucket::createTable($pdo);

echo "Table `course_bucket` created successfully.";
