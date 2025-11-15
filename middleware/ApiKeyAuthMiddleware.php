<?php

class ApiKeyAuthMiddleware {
    public static function handle() {
        $headers = getallheaders();
        $apiKey = isset($headers['X-API-KEY']) ? $headers['X-API-KEY'] : null;

        // Get the valid API key from the environment variable
        $validApiKey = getenv('API_KEY');

        if (!$apiKey || $apiKey !== $validApiKey) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
            exit();
        }
    }
}
