<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth {
    private $secret_key;
    private $algorithm;
    
    public function __construct() {
        // Load from environment or config
        $this->secret_key = $_ENV['JWT_SECRET'] ?? "your_super_secret_key_change_this";
        $this->algorithm = "HS256";
    }
    
    public function generateToken($user_data) {
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60); // 1 hour
        
        $payload = array(
            "iss" => $_SERVER['HTTP_HOST'] ?? "localhost",
            "aud" => $_SERVER['HTTP_HOST'] ?? "localhost", 
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "data" => $user_data
        );
        
        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }
    
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    
    private function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if ($requestHeaders) {
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
        }
        
        return $headers;
    }
}
?>