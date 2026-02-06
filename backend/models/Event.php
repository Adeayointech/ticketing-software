<?php
namespace Models;

class Event {
    private $conn;
    private $table = 'events';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 (organizer_id, title, description, venue, address, event_date, end_date, image_url, status) 
                 VALUES (:organizer_id, :title, :description, :venue, :address, :event_date, :end_date, :image_url, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':organizer_id', $data['organizer_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':venue', $data['venue']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':event_date', $data['event_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getAll($filters = []) {
        $query = "SELECT e.*, u.first_name, u.last_name, u.email as organizer_email,
                        (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold
                 FROM " . $this->table . " e
                 LEFT JOIN users u ON e.organizer_id = u.id
                 WHERE 1=1";
        
        if (isset($filters['status'])) {
            $query .= " AND e.status = :status";
        }
        
        if (isset($filters['upcoming'])) {
            $query .= " AND e.event_date >= NOW()";
        }
        
        if (isset($filters['search'])) {
            $query .= " AND (e.title LIKE :search OR e.description LIKE :search OR e.venue LIKE :search)";
        }
        
        $query .= " ORDER BY e.event_date ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $stmt->bindParam(':search', $search);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT e.*, u.first_name, u.last_name, u.email as organizer_email,
                        (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold
                 FROM " . $this->table . " e
                 LEFT JOIN users u ON e.organizer_id = u.id
                 WHERE e.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getByOrganizer($organizerId) {
        $query = "SELECT e.*,
                        (SELECT COUNT(*) FROM tickets WHERE event_id = e.id) as tickets_sold,
                        (SELECT SUM(o.total_amount) FROM orders o WHERE o.event_id = e.id AND o.payment_status = 'completed') as total_revenue
                 FROM " . $this->table . " e
                 WHERE e.organizer_id = :organizer_id
                 ORDER BY e.event_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':organizer_id', $organizerId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                 SET title = :title, description = :description, venue = :venue, 
                     address = :address, event_date = :event_date, end_date = :end_date,
                     image_url = :image_url, status = :status, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':venue', $data['venue']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':event_date', $data['event_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
