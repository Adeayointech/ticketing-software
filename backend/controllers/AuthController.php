<?php
namespace Controllers;

use Config\Database;
use Models\User;
use Utils\JWTHandler;
use Utils\Response;

class AuthController {
    private $db;
    private $userModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function register($data) {
        // Validate input
        $errors = [];
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (!empty($errors)) {
            Response::error('Validation failed', 422, $errors);
        }
        
        // Check if user exists
        if ($this->userModel->findByEmail($data['email'])) {
            Response::error('Email already registered', 409);
        }
        
        // Create user
        $userData = [
            'email' => $data['email'],
            'password_hash' => $this->userModel->hashPassword($data['password']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? 'attendee',
            'phone' => $data['phone'] ?? null
        ];
        
        $userId = $this->userModel->create($userData);
        
        if (!$userId) {
            Response::error('Failed to create user', 500);
        }
        
        // Generate token
        $token = $this->jwtHandler->generateToken($userId, $data['email'], $userData['role']);
        
        $user = $this->userModel->findById($userId);
        
        Response::success([
            'token' => $token,
            'user' => $user
        ], 'Registration successful', 201);
    }
    
    public function login($data) {
        // Validate input
        if (empty($data['email']) || empty($data['password'])) {
            Response::error('Email and password are required', 400);
        }
        
        // Find user
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            Response::error('Invalid credentials', 401);
        }
        
        // Verify password
        if (!$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
            Response::error('Invalid credentials', 401);
        }
        
        // Generate token
        $token = $this->jwtHandler->generateToken($user['id'], $user['email'], $user['role']);
        
        // Remove password from response
        unset($user['password_hash']);
        
        Response::success([
            'token' => $token,
            'user' => $user
        ], 'Login successful');
    }
    
    public function getCurrentUser() {
        try {
            $userData = $this->jwtHandler->getCurrentUser();
            $user = $this->userModel->findById($userData->id);
            
            if (!$user) {
                Response::notFound('User not found');
            }
            
            Response::success(['user' => $user]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
}
