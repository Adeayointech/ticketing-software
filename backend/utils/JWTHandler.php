<?php
namespace Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private $secret;
    private $algorithm;
    
    public function __construct() {
        $this->secret = JWT_SECRET;
        $this->algorithm = JWT_ALGORITHM;
    }
    
    public function generateToken($userId, $email, $role) {
        $issuedAt = time();
        $expirationTime = $issuedAt + JWT_EXPIRATION;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'id' => $userId,
                'email' => $email,
                'role' => $role
            ]
        ];
        
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }
    
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $decoded->data;
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired token', 401);
        }
    }
    
    public function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            throw new \Exception('No authorization header', 401);
        }
        
        $authHeader = $headers['Authorization'];
        $arr = explode(' ', $authHeader);
        
        if (count($arr) !== 2 || $arr[0] !== 'Bearer') {
            throw new \Exception('Invalid authorization header format', 401);
        }
        
        return $arr[1];
    }
    
    public function getCurrentUser() {
        $token = $this->getTokenFromHeader();
        return $this->validateToken($token);
    }
}
