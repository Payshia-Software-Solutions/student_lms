<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


// utils/JwtHelper.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private static $secret_key;
    private static $algo = 'HS256';
    private static $expire = 86400 ; // 1 hour   = 3600

    public static function generateToken($user)
    {
        $payload = [
            'iss' => 'erp_server',
            'iat' => time(),
            'exp' => time() + self::$expire,
            'sub' => $user['id'],
            'email' => $user['email'],
        ];
        self::loadSecret();
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
            $envDir = __DIR__ . '/../';
            if (file_exists($envDir . '.env')) {
                $dotenv = Dotenv::createImmutable($envDir);
                $dotenv->load();
            }
            if (getenv('JWT_SECRET')) {
                self::$secret_key = getenv('JWT_SECRET');
            } elseif (isset($_ENV['JWT_SECRET'])) {
                self::$secret_key = $_ENV['JWT_SECRET'];
            }
        }
    }
}
