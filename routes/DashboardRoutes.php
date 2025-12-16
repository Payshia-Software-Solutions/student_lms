<?php

require_once __DIR__ . '/../controllers/DashboardController.php';

$pdo = $GLOBALS['pdo'];
$dashboardController = new DashboardController($pdo);

return [
    'GET /dashboard/counts' => [
        'handler' => [$dashboardController, 'getDashboardCounts'],
        'auth' => 'private'
    ],
];
