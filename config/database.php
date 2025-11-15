
text/x-generic database.php ( PHP script, ASCII text, with CRLF line terminators )
<?php
// config/database.php

$host = 'localhost';
$db   = 'student_lms';
$user = 'root';
$pass = "";
$charset = 'utf8mb4';



// this is main 

// $host = '91.204.209.19';
// $db   = 'payshiac_erp_server';
// $user = 'payshiac_erp_server';
// $pass = "MBAl%WyfN0Bqc~{m";
// $charset = 'utf8mb4';


// $host = '91.204.209.19';
// $db   = 'payshiac_student_lms';
// $user = 'payshiac_student_lms';
// $pass = "[]M.ujKl{b-a{ASr";
// $charset = 'utf8mb4';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}


// Connect to Webserver
// Test

// $host = '91.204.209.19';
// $db   = 'payshiac_kdu_test';
// $user = 'payshiac';
// $pass = "1999tr@thilina";
// $charset = 'utf8mb4';
// $dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";