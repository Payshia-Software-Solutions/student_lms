<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CourseBucket.php';
require_once __DIR__ . '/../models/CourseBucketContent.php';
require_once __DIR__ . '/../models/StudentPaymentCourse.php';
require_once __DIR__ . '/../models/PaymentRequest.php';

$database = new Database();
$pdo = $database->connect();

CourseBucket::createTable($pdo);
CourseBucketContent::createTable($pdo);
StudentPaymentCourse::createTable($pdo);
PaymentRequest::createTable($pdo);

echo "Tables `course_bucket`, `course_bucket_content`, `student_payment_course` and `payment_request` created successfully.";
