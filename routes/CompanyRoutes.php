<?php

require_once __DIR__ . '/../controllers/CompanyController.php';

class CompanyRoutes
{
    private $companyController;

    public function __construct($pdo)
    {
        $this->companyController = new CompanyController($pdo);
    }

    public function handleRequest($method, $endpoint)
    {
        switch ($method) {
            case 'POST':
                if ($endpoint === '/company/create') {
                    $this->companyController->createRecord();
                } else {
                    $this->handleNotFound();
                }
                break;
            case 'GET':
                if ($endpoint === '/company/all') {
                    $this->companyController->getAllRecords();
                } 
                // --- NEW ROUTE ---
                else if ($endpoint === '/company') {
                    $this->companyController->getCompanyById();
                }
                else {
                    $this->handleNotFound();
                }
                break;
            case 'PUT':
                if (preg_match('/^\/company\/(\d+)\/update$/', $endpoint, $matches)) {
                    $id = $matches[1];
                    $this->companyController->updateRecord($id);
                } else {
                    $this->handleNotFound();
                }
                break;
            case 'DELETE':
                if (preg_match('/^\/company\/(\d+)\/delete$/', $endpoint, $matches)) {
                    $id = $matches[1];
                    $this->companyController->deleteRecord($id);
                } else {
                    $this->handleNotFound();
                }
                break;
            default:
                $this->handleNotFound();
                break;
        }
    }

    private function handleNotFound()
    {
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found."));
    }
}
