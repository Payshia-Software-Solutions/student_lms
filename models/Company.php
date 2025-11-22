
<?php

class Company
{
    private $conn;

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

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function createTable($db)
    {
        $query = "CREATE TABLE IF NOT EXISTS company (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL,
            address TEXT,
            phone VARCHAR(20),
            email VARCHAR(255) UNIQUE,
            website VARCHAR(255),
            vision TEXT,
            mission TEXT,
            founder_message TEXT,
            logo VARCHAR(255),
            registration_number VARCHAR(100) UNIQUE,
            company_trifix VARCHAR(10) UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Table Creation Error: " . $e->getMessage());
        }
    }

    public function create()
    {
        $query = "INSERT INTO company SET company_name = :company_name, address = :address, phone = :phone, email = :email, website = :website, vision = :vision, mission = :mission, founder_message = :founder_message, logo = :logo, registration_number = :registration_number, company_trifix = :company_trifix";

        $stmt = $this->conn->prepare($query);

        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->vision = htmlspecialchars(strip_tags($this->vision));
        $this->mission = htmlspecialchars(strip_tags($this->mission));
        $this->founder_message = htmlspecialchars(strip_tags($this->founder_message));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        $this->registration_number = htmlspecialchars(strip_tags($this->registration_number));
        $this->company_trifix = htmlspecialchars(strip_tags($this->company_trifix));

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
        $query = "SELECT * FROM company";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id)
    {
        $query = "SELECT * FROM company WHERE id = ?";
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
            return true;
        }
        return false;
    }

    public function getTrifixById($id)
    {
        $query = "SELECT company_trifix FROM company WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            return $row['company_trifix'];
        }
        return null;
    }

}
