<?php

require_once __DIR__ . '/../controllers/StudentOrderController.php';

$pdo = $GLOBALS['pdo'];
$studentOrderController = new StudentOrderController($pdo);

return [
    'GET /student-orders/' => [
        'handler' => [$studentOrderController, 'getAllRecords'],
        'auth' => 'admin'
    ],
    'GET /student-orders/{id}/' => [
        'handler' => function ($id) use ($studentOrderController) {
            $studentOrderController->getRecordById($id);
        },
        'auth' => 'user'
    ],
    'GET /student-orders/student/{student_id}/' => [
        'handler' => function ($student_id) use ($studentOrderController) {
            $studentOrderController->getOrdersByStudent($student_id);
        },
        'auth' => 'user'
    ],
    'POST /student-orders/' => [
        'handler' => [$studentOrderController, 'createRecord'],
        'auth' => 'user'
    ],
    'PUT /student-orders/{id}/status/' => [
        'handler' => function ($id) use ($studentOrderController) {
            $studentOrderController->updateOrderStatus($id);
        },
        'auth' => 'admin'
    ],
    'DELETE /student-orders/{id}/' => [
        'handler' => function ($id) use ($studentOrderController) {
            $studentOrderController->deleteRecord($id);
        },
        'auth' => 'admin'
    ]
];
