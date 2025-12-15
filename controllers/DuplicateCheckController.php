<?php

require_once __DIR__ . '/../models/PaymentRequest.php';

class DuplicateCheckController
{
    private $paymentRequest;

    public function __construct($pdo)
    {
        $this->paymentRequest = new PaymentRequest($pdo);
    }

    public function checkDuplicateByHash($hash)
    {
        $stmt = $this->paymentRequest->getByFilters(['hash' => $hash]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($records) > 1) {
            echo json_encode(['status' => 'success', 'duplicate' => true, 'data' => $records]);
        } else {
            echo json_encode(['status' => 'success', 'duplicate' => false, 'data' => $records]);
        }
    }

    public function checkDuplicateByHashAndId($hash, $id)
    {
        $stmt = $this->paymentRequest->getByFilters(['hash' => $hash, 'not_id' => $id]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($records) > 0) {
            echo json_encode(['status' => 'success', 'duplicate' => true, 'data' => $records]);
        } else {
            echo json_encode(['status' => 'success', 'duplicate' => false, 'data' => []]);
        }
    }
}
