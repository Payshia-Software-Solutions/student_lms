<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CourseBucket.php';
require_once __DIR__ . '/../models/CourseBucketContent.php';

$database = new Database();
$pdo = $database->connect();

CourseBucket::createTable($pdo);
CourseBucketContent::createTable($pdo);

echo "Tables `course_bucket` and `course_bucket_content` created successfully.";
