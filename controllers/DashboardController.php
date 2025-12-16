<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/PaymentRequest.php';

class DashboardController
{
    private $user;
    private $course;
    private $paymentRequest;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
        $this->course = new Course($pdo);
        $this->paymentRequest = new PaymentRequest($pdo);
    }

    public function getDashboardCounts()
    {
        $studentCount = $this->user->getCountByStatus('student');
        $courseCount = $this->course->getTotalCount();
        $pendingPaymentRequestCount = $this->paymentRequest->getCountByStatus('pending');

        $counts = [
            'student_count' => $studentCount,
            'course_count' => $courseCount,
            'pending_payment_request_count' => $pendingPaymentRequestCount
        ];

        echo json_encode(['status' => 'success', 'data' => $counts]);
    }
}
