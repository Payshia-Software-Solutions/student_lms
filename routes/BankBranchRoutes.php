   <?php

require_once __DIR__ . '/../controllers/BankBranchController.php';

$bankBranchController = new BankBranchController($GLOBALS['pdo']);

return [
    'GET /bank_branches' => [
        'handler' => [$bankBranchController, 'getAllRecords'],
        'auth' => 'public'
    ],
    'GET /bank_branches/{id}' => [
        'handler' => [$bankBranchController, 'getRecordById'],
        'auth' => 'public'
    ],
    'POST /bank_branches' => [
        'handler' => [$bankBranchController, 'createRecord'],
        'auth' => 'private'
    ],
    'PUT /bank_branches/{id}' => [
        'handler' => [$bankBranchController, 'updateRecord'],
        'auth' => 'private'
    ],
    'DELETE /bank_branches/{id}' => [
        'handler' => [$bankBranchController, 'deleteRecord'],
        'auth' => 'private'
    ],
];
