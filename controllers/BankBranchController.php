<?php

require_once __DIR__ . '/../models/BankBranch.php';

class BankBranchController
{
    private $bankBranch;

    public function __construct($pdo)
    {
        $this->bankBranch = new BankBranch($pdo);
    }

    public function getAllRecords()
    {
        $bank_id = isset($_GET['bank_id']) ? $_GET['bank_id'] : null;

        if ($bank_id) {
            $stmt = $this->bankBranch->getByBankId($bank_id);
        } else {
            $stmt = $this->bankBranch->getAll();
        }

        $bankBranches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $bankBranches]);
    }

    public function getRecordById($id)
    {
        $bankBranch = $this->bankBranch->getById($id);
        if ($bankBranch) {
            echo json_encode(['status' => 'success', 'data' => $bankBranch]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Bank branch not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->bankBranch->create($data);
        if ($newId) {
            $record = $this->bankBranch->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Bank branch created successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create bank branch']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->bankBranch->update($id, $data)) {
            $record = $this->bankBranch->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'Bank branch updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update bank branch']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->bankBranch->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'Bank branch deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete bank branch']);
        }
    }
}
?>