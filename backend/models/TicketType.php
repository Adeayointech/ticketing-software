<?php
namespace Models;

class TicketType {
    private $conn;
    private $table = 'ticket_types';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 (event_id, name, description, price, quantity, sale_start_date, sale_end_date) 
                 VALUES (:event_id, :name, :description, :price, :quantity, :sale_start_date, :sale_end_date)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':event_id', $data['event_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':sale_start_date', $data['sale_start_date']);
        $stmt->bindParam(':sale_end_date', $data['sale_end_date']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getByEvent($eventId) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE event_id = :event_id 
                 ORDER BY price ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':event_id', $eventId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function updateQuantitySold($id, $quantity) {
        $query = "UPDATE " . $this->table . " 
                 SET quantity_sold = quantity_sold + :quantity 
                 WHERE id = :id AND (quantity_sold + :quantity) <= quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        
        return $stmt->execute();
    }
    
    public function checkAvailability($id, $quantity) {
        $query = "SELECT (quantity - quantity_sold) as available 
                 FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result && $result['available'] >= $quantity;
    }
}
