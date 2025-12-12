<?php

require_once __DIR__ . '/../models/City.php';

class CityController
{
    private $city;

    public function __construct($pdo)
    {
        $this->city = new City($pdo);
    }

    public function getAllRecords()
    {
        if(isset($_GET['district_id'])){
            $cities = $this->city->getByDistrictId($_GET['district_id']);
            echo json_encode(['status' => 'success', 'data' => $cities]);
        }else{
            $stmt = $this->city->getAll();
            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $cities]);
        }
    }

    public function getRecordById($id)
    {
        $city = $this->city->getById($id);
        if ($city) {
            echo json_encode(['status' => 'success', 'data' => $city]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'City not found']);
        }
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->city->create($data);
        if ($newId) {
            $record = $this->city->getById($newId);
            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'City created successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to create city']);
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->city->update($id, $data)) {
            $record = $this->city->getById($id);
            echo json_encode(['status' => 'success', 'message' => 'City updated successfully', 'data' => $record]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to update city']);
        }
    }

    public function deleteRecord($id)
    {
        if ($this->city->delete($id)) {
            echo json_encode(['status' => 'success', 'message' => 'City deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to delete city']);
        }
    }
}
?>