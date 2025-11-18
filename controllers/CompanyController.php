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

    public function createTable()
    {
        Company::createTable($this->db);
        echo "Company table created successfully.";
    }

    public function createRecord()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $newId = $this->company->create($data);
        if ($newId) {
            if ($this->company->getById($newId)) {
                $company_item = array(
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
                    "company_trifix" => $this->company->company_trifix,
                    "created_at" => $this->company->created_at,
                    "updated_at" => $this->company->updated_at
                );
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Company was created.",
                    "data" => $company_item
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to retrieve created company."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create company."));
        }
    }

    public function getAllRecords()
    {
        $stmt = $this->company->getAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $companies_arr = array();
            $companies_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $company_item = array(
                    "id" => $id,
                    "company_name" => $company_name,
                    "address" => $address,
                    "phone" => $phone,
                    "email" => $email,
                    "website" => $website,
                    "vision" => $vision,
                    "mission" => $mission,
                    "founder_message" => $founder_message,
                    "logo" => $logo,
                    "registration_number" => $registration_number,
                    "company_trifix" => $company_trifix
                );
                array_push($companies_arr["records"], $company_item);
            }

            http_response_code(200);
            echo json_encode($companies_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No companies found."));
        }
    }

    public function getRecordById($id)
    {
        if ($this->company->getById($id)) {
            $company_item = array(
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
            );
            http_response_code(200);
            echo json_encode($company_item);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Company not found."));
        }
    }

    public function updateRecord($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->company->update($id, $data)) {
            if ($this->company->getById($id)) {
                $company_item = array(
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
                    "company_trifix" => $this->company->company_trifix,
                    "created_at" => $this->company->created_at,
                    "updated_at" => $this->company->updated_at
                );
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Company was updated.",
                    "data" => $company_item
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Company not found after update."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update company."));
        }
    }

    public function deleteRecord($id)
    {
        if ($this->company->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Company was deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete company."));
        }
    }
}
