<?php

require_once __DIR__ . '/../utils/JwtHelper.php';

class JwtAuthMiddleware {
    public static function handle() {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Missing or invalid Authorization header']);
            exit();
        }

        $token = $matches[1];
        $jwtPayload = JwtHelper::validateToken($token);

        if (!$jwtPayload) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
            exit();
        }

        // Set jwtPayload to global for use in controllers
        $GLOBALS['jwtPayload'] = $jwtPayload;
    }
}
