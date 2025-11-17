<?php
// utils/JwtHelper.php

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private static $secret_key;
    private static $algo = 'HS256';
    private static $expire = 86400 ; // 1 day

    public static function generateToken($user)
    {
        self::loadSecret();
        $payload = [
            'iss' => 'erp_server',
            'iat' => time(),
            'exp' => time() + self::$expire,
            'sub' => $user['id'],
            'email' => $user['email'],
        ];
        return JWT::encode($payload, self::$secret_key, self::$algo);
    }

    public static function validateToken($token)
    {
        self::loadSecret();
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algo));
            return (array)$decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    private static function loadSecret()
    {
        if (!self::$secret_key) {
            self::$secret_key = getenv('JWT_SECRET');
        }
    }
}
