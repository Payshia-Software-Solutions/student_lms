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

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get the first company record
    public function get()
    {
        $query = "SELECT * FROM company LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            // Set properties
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
            return true;
        }

        return false;
    }

    // Get company trifix by ID
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
