<?php
namespace Models;

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 (email, password_hash, first_name, last_name, role, phone) 
                 VALUES (:email, :password_hash, :first_name, :last_name, :role, :phone)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $data['password_hash']);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':phone', $data['phone']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $query = "SELECT id, email, first_name, last_name, role, phone, created_at, password_hash 
                 FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $user = $stmt->fetch();
        if ($user) {
            // Combine first_name and last_name into name
            $user['name'] = trim($user['first_name'] . ' ' . $user['last_name']);
            $user['password'] = $user['password_hash'];
        }
        
        return $user;
    }
    
    public function getById($id) {
        return $this->findById($id);
    }
    
    public function getByEmail($email) {
        return $this->findByEmail($email);
    }
    
    public function update($data) {
        $fields = [];
        $params = [':id' => $data['id']];
        
        // Split name into first_name and last_name if provided
        if (isset($data['name'])) {
            $nameParts = explode(' ', $data['name'], 2);
            $fields[] = "first_name = :first_name";
            $fields[] = "last_name = :last_name";
            $params[':first_name'] = $nameParts[0];
            $params[':last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password_hash = :password";
            $params[':password'] = $data['password'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }

    public function oldUpdate($id, $data) {
        $query = "UPDATE " . $this->table . " 
                 SET first_name = :first_name, last_name = :last_name, 
                     phone = :phone, updated_at = CURRENT_TIMESTAMP 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        
        return $stmt->execute();
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
