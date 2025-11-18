<?php

class Company
{
    private $conn;

    // Company Properties
    public $id;
    public $company_name;
    public $address;
    public $phone;
    public $email;
    public $website;
    public $vision;
    public $mission;
    public $founder_message;
    public $logo;
    public $registration_number;
    public $company_trifix;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS company (\n            id INT AUTO_INCREMENT PRIMARY KEY,\n            company_name VARCHAR(255) NOT NULL,\n            address TEXT,\n            phone VARCHAR(20),\n            email VARCHAR(255) UNIQUE,\n            website VARCHAR(255),\n            vision TEXT,\n            mission TEXT,\n            founder_message TEXT,\n            logo VARCHAR(255),\n            registration_number VARCHAR(100) UNIQUE,\n            company_trifix VARCHAR(10) UNIQUE,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            deleted_at TIMESTAMP NULL\n        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    public function create($data)
    {
        $query = "INSERT INTO company (company_name, address, phone, email, website, vision, mission, founder_message, logo, registration_number, company_trifix) VALUES (:company_name, :address, :phone, :email, :website, :vision, :mission, :founder_message, :logo, :registration_number, :company_trifix)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->company_name = htmlspecialchars(strip_tags($data['company_name']));
        $this->address = htmlspecialchars(strip_tags($data['address']));
        $this->phone = htmlspecialchars(strip_tags($data['phone']));
        $this->email = htmlspecialchars(strip_tags($data['email']));
        $this->website = htmlspecialchars(strip_tags($data['website']));
        $this->vision = htmlspecialchars(strip_tags($data['vision']));
        $this->mission = htmlspecialchars(strip_tags($data['mission']));
        $this->founder_message = htmlspecialchars(strip_tags($data['founder_message']));
        $this->logo = htmlspecialchars(strip_tags($data['logo']));
        $this->registration_number = htmlspecialchars(strip_tags($data['registration_number']));
        $this->company_trifix = htmlspecialchars(strip_tags($data['company_trifix']));

        $stmt->bindParam(':company_name', $this->company_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':vision', $this->vision);
        $stmt->bindParam(':mission', $this->mission);
        $stmt->bindParam(':founder_message', $this->founder_message);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':registration_number', $this->registration_number);
        $stmt->bindParam(':company_trifix', $this->company_trifix);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll()
    {
        $query = "SELECT * FROM company WHERE deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM company WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->company_name = $row['company_name'];
            $this->address = $row['address'];
            $this->phone = $row['phone'];
            $this->email = $row['email'];
            $this->website = $row['website'];
            $this->vision = $row['vision'];
            $this->mission = $row['mission'];
            $this->founder_message = $row['founder_message'];
            $this->logo = $row['logo'];
            $this->registration_number = $row['registration_number'];
            $this->company_trifix = $row['company_trifix'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update($id, $data)
    {
        $query = "UPDATE company SET company_name = :company_name, address = :address, phone = :phone, email = :email, website = :website, vision = :vision, mission = :mission, founder_message = :founder_message, logo = :logo, registration_number = :registration_number, company_trifix = :company_trifix WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->id = htmlspecialchars(strip_tags($id));
        $this->company_name = htmlspecialchars(strip_tags($data['company_name']));
        $this->address = htmlspecialchars(strip_tags($data['address']));
        $this->phone = htmlspecialchars(strip_tags($data['phone']));
        $this->email = htmlspecialchars(strip_tags($data['email']));
        $this->website = htmlspecialchars(strip_tags($data['website']));
        $this->vision = htmlspecialchars(strip_tags($data['vision']));
        $this->mission = htmlspecialchars(strip_tags($data['mission']));
        $this->founder_message = htmlspecialchars(strip_tags($data['founder_message']));
        $this->logo = htmlspecialchars(strip_tags($data['logo']));
        $this->registration_number = htmlspecialchars(strip_tags($data['registration_number']));
        $this->company_trifix = htmlspecialchars(strip_tags($data['company_trifix']));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':company_name', $this->company_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':vision', $this->vision);
        $stmt->bindParam(':mission', $this->mission);
        $stmt->bindParam(':founder_message', $this->founder_message);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':registration_number', $this->registration_number);
        $stmt->bindParam(':company_trifix', $this->company_trifix);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $query = "UPDATE company SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTrifixById($company_id)
    {
        $query = "SELECT company_trifix FROM company WHERE id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['company_trifix'];
        }

        return null;
    }
}
