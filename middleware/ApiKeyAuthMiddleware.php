<?php

class ApiKeyAuthMiddleware {
    public static function handle() {
        $headers = getallheaders();
        $apiKey = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : null;

        // TODO: Store API keys securely, e.g., in a database or environment variable
        $validApiKeys = [
            'your-secure-api-key' // Replace with a strong, randomly generated key
        ];

        if (!$apiKey || !in_array($apiKey, $validApiKeys)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
            exit();
        }
    }
}
