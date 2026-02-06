<?php
namespace Models;

class Order {
    private $conn;
    private $table = 'orders';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 (user_id, event_id, order_number, total_amount, payment_status, payment_method, transaction_id) 
                 VALUES (:user_id, :event_id, :order_number, :total_amount, :payment_status, :payment_method, :transaction_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':event_id', $data['event_id']);
        $stmt->bindParam(':order_number', $data['order_number']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':payment_status', $data['payment_status']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->bindParam(':transaction_id', $data['transaction_id']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function getById($id) {
        $query = "SELECT o.*, e.title as event_title, e.venue, e.event_date
                 FROM " . $this->table . " o
                 LEFT JOIN events e ON o.event_id = e.id
                 WHERE o.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function getByUser($userId) {
        $query = "SELECT o.*, e.title as event_title, e.venue, e.event_date, e.image_url as event_image,
                        (SELECT COUNT(*) FROM tickets WHERE order_id = o.id) as ticket_count
                 FROM " . $this->table . " o
                 LEFT JOIN events e ON o.event_id = e.id
                 WHERE o.user_id = :user_id
                 ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function updatePaymentStatus($id, $status, $transactionId = null) {
        $query = "UPDATE " . $this->table . " 
                 SET payment_status = :status";
        
        if ($transactionId) {
            $query .= ", transaction_id = :transaction_id";
        }
        
        $query .= ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        
        if ($transactionId) {
            $stmt->bindParam(':transaction_id', $transactionId);
        }
        
        return $stmt->execute();
    }
    
    public function generateOrderNumber() {
        return 'ORD' . date('Ymd') . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }
}
