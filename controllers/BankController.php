<?php

require_once __DIR__ . '/../models/Bank.php';

class BankController
{
    private $bank;

    public function __construct($pdo)
    {
        $this->bank = new Bank($pdo);
    }

    public function getAllRecords()
    {
        $stmt = $this->bank->getAll();
        $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $banks]);
    }

    public function getRecordById($id)
    {
        $bank = $this->bank->getById($id);
        if ($bank) {
            echo json_encode(['status' => 'success', 'data' => $bank]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Bank not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->bank->create($data);
        if ($newId) {
            $record = $this->bank->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Bank created successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create bank']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->bank->update($id, $data)) {
            $record = $this->bank->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Bank updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update bank']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->bank->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Bank deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete bank']);
        }
    }
}
?>