<?php
namespace Models;

class Ticket {
    private $conn;
    private $table = 'tickets';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 (order_id, user_id, event_id, ticket_type_id, ticket_number, qr_code, status) 
                 VALUES (:order_id, :user_id, :event_id, :ticket_type_id, :ticket_number, :qr_code, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':order_id', $data['order_id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':event_id', $data['event_id']);
        $stmt->bindParam(':ticket_type_id', $data['ticket_type_id']);
        $stmt->bindParam(':ticket_number', $data['ticket_number']);
        $stmt->bindParam(':qr_code', $data['qr_code']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getById($id) {
        $query = "SELECT t.*, e.title as event_title, e.venue, e.address, e.event_date, e.end_date,
                        tt.name as ticket_type_name, tt.price,
                        u.first_name, u.last_name, u.email
                 FROM " . $this->table . " t
                 LEFT JOIN events e ON t.event_id = e.id
                 LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                 LEFT JOIN users u ON t.user_id = u.id
                 WHERE t.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getByUser($userId) {
        $query = "SELECT t.*, e.title as event_title, e.venue, e.event_date, e.image_url as event_image,
                        tt.name as ticket_type_name, tt.price,
                        o.order_number
                 FROM " . $this->table . " t
                 LEFT JOIN events e ON t.event_id = e.id
                 LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                 LEFT JOIN orders o ON t.order_id = o.id
                 WHERE t.user_id = :user_id
                 ORDER BY e.event_date DESC";
        
        error_log("Executing getByUser query for user_id: $userId");
        error_log("Query: $query");
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        error_log("Found " . count($results) . " tickets for user $userId");
        
        if (count($results) > 0) {
            error_log("First ticket data: " . json_encode($results[0]));
        }
        
        return $results;
    }
    
    public function getByOrder($orderId) {
        $query = "SELECT t.*, tt.name as ticket_type_name, tt.price
                 FROM " . $this->table . " t
                 LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                 WHERE t.order_id = :order_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getByTicketNumber($ticketNumber) {
        $query = "SELECT t.*, e.title as event_title, e.venue, e.event_date,
                        tt.name as ticket_type_name,
                        u.first_name, u.last_name, u.email
                 FROM " . $this->table . " t
                 LEFT JOIN events e ON t.event_id = e.id
                 LEFT JOIN ticket_types tt ON t.ticket_type_id = tt.id
                 LEFT JOIN users u ON t.user_id = u.id
                 WHERE LOWER(TRIM(t.ticket_number)) = LOWER(TRIM(:ticket_number)) LIMIT 1";
        $ticketNumber = trim($ticketNumber);
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ticket_number', $ticketNumber);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " 
                 SET status = :status";
        
        if ($status === 'used') {
            $query .= ", used_at = CURRENT_TIMESTAMP";
        }
        
        $query .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
    
    public function updateQRCode($id, $qrCode) {
        $query = "UPDATE " . $this->table . " 
                 SET qr_code = :qr_code, updated_at = CURRENT_TIMESTAMP 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':qr_code', $qrCode);
        
        return $stmt->execute();
    }
    
    public function generateTicketNumber() {
        return 'TKT' . date('Ymd') . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
    }
}
