<?php

class LocationController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getProvinces()
    {
        $query = "SELECT id, name FROM provinces WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($provinces);
    }

    public function getDistricts($province_id)
    {
        $query = "SELECT id, name FROM districts WHERE province_id = :province_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':province_id', $province_id);
        $stmt->execute();
        $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($districts);
    }

    public function getCitiesByDistrict($district_id)
    {
        $query = "SELECT id, name FROM cities WHERE district_id = :district_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':district_id', $district_id);
        $stmt->execute();
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cities);
    }
    
    public function getAllCities()
    {
        $query = "SELECT c.id, c.name, c.district_id, d.name as district_name, p.id as province_id, p.name as province_name FROM cities c JOIN districts d ON c.district_id = d.id JOIN provinces p ON d.province_id = p.id WHERE c.deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cities);
    }

    public function getCity($id)
    {
        $query = "SELECT id, name, district_id FROM cities WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $city = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($city);
    }

    public function createCity()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->name) || !isset($data->district_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Unable to create city. Data is incomplete.']);
            return;
        }

        $query = "INSERT INTO cities (name, district_id) VALUES (:name, :district_id)";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':district_id', $data->district_id);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'City created successfully.']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to create city.']);
        }
    }

    public function updateCity($id)
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->name) || !isset($data->district_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'Unable to update city. Data is incomplete.']);
            return;
        }

        $query = "UPDATE cities SET name = :name, district_id = :district_id, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':district_id', $data->district_id);

        if ($stmt->execute()) {
            if ($stmt->rowCount()) {
                http_response_code(200);
                echo json_encode(['message' => 'City updated successfully.']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'City not found.']);
            }
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to update city.']);
        }
    }

    public function deleteCity($id)
    {
        // Soft delete the city by setting the deleted_at timestamp
        $query = "UPDATE cities SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            if ($stmt->rowCount()) {
                http_response_code(200);
                echo json_encode(['message' => 'City deleted successfully.']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'City not found.']);
            }
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to delete city.']);
        }
    }
}
