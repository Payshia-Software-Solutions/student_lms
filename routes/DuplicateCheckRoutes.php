<?php

require_once __DIR__ . '/../controllers/DuplicateCheckController.php';

$duplicateCheckController = new DuplicateCheckController($GLOBALS['pdo']);

return [
    'GET /duplicate/hash/{hash}' => [
        'handler' => function ($hash) use ($duplicateCheckController) {
            $duplicateCheckController->checkDuplicateByHash($hash);
        },
        'auth' => 'private'
    ],
    'GET /duplicate/hash/{hash}/id/{id}' => [
        'handler' => function ($hash, $id) use ($duplicateCheckController) {
            $duplicateCheckController->checkDuplicateByHashAndId($hash, $id);
        },
        'auth' => 'private'
    ],
];
