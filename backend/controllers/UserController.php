<?php
namespace Controllers;

use Config\Database;
use Models\User;
use Utils\JWTHandler;
use Utils\Response;

class UserController {
    private $db;
    private $userModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function updateProfile($input) {
        // Verify JWT token
        $userId = $this->jwtHandler->getUserIdFromToken();
        if (!$userId) {
            Response::unauthorized('Unauthorized access');
        }
        
        // Validate required fields
        if (empty($input['name']) || empty($input['email'])) {
            Response::badRequest('Name and email are required');
        }
        
        // Get current user
        $user = $this->userModel->getById($userId);
        if (!$user) {
            Response::notFound('User not found');
        }
        
        // Check if email is already taken by another user
        if ($input['email'] !== $user['email']) {
            $existingUser = $this->userModel->getByEmail($input['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                Response::badRequest('Email already in use');
            }
        }
        
        // Update data
        $updateData = [
            'id' => $userId,
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? $user['phone']
        ];
        
        // Handle password change if provided
        if (!empty($input['new_password'])) {
            // Verify current password
            if (empty($input['current_password'])) {
                Response::badRequest('Current password is required to change password');
            }
            
            if (!password_verify($input['current_password'], $user['password'])) {
                Response::badRequest('Current password is incorrect');
            }
            
            // Hash new password
            $updateData['password'] = password_hash($input['new_password'], PASSWORD_DEFAULT);
        }
        
        // Update user
        if ($this->userModel->update($updateData)) {
            // Get updated user data
            $updatedUser = $this->userModel->getById($userId);
            
            // Remove password from response
            unset($updatedUser['password']);
            
            Response::success([
                'message' => 'Profile updated successfully',
                'user' => $updatedUser
            ]);
        } else {
            Response::error('Failed to update profile');
        }
    }
}
