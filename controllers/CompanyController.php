<?php

require_once __DIR__ . '/../models/Company.php';

class CompanyController
{
    private $db;
    private $company;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->company = new Company($this->db);
    }

    public function getRecord()
    {
        if ($this->company->get()) {
            $company_data = [
                "id" => $this->company->id,
                "company_name" => $this->company->company_name,
                "address" => $this->company->address,
                "phone" => $this->company->phone,
                "email" => $this->company->email,
                "website" => $this->company->website,
                "vision" => $this->company->vision,
                "mission" => $this->company->mission,
                "founder_message" => $this->company->founder_message,
                "logo" => $this->company->logo,
                "registration_number" => $this->company->registration_number,
                "company_trifix" => $this->company->company_trifix
            ];

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data' => $company_data
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Company details not found.'
            ]);
        }
    }
}
