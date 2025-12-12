<?php

require_once __DIR__ . '/../models/District.php';

class DistrictController
{
    private $district;

    public function __construct($pdo)
    {
        $this->district = new District($pdo);
    }

    public function getAllRecords()
    {
        if(isset($_GET['province_id'])){
            $districts = $this->district->getByProvinceId($_GET['province_id']);
            echo json_encode(['status' => 'success', 'data' => $districts]);
        }else{
            $stmt = $this->district->getAll();
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $districts]);
        }
    }

    public function getRecordById($id)
    {
        $district = $this->district->getById($id);
        if ($district) {
            echo json_encode(['status' => 'success', 'data' => $district]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'District not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->district->create($data);
        if ($newId) {
            $record = $this->district->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'District created successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create district']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->district->update($id, $data)) {
            $record = $this->district->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'District updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update district']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->district->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'District deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete district']);
        }
    }
}
?>