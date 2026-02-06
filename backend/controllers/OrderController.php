<?php
namespace Controllers;

use Config\Database;
use Models\Order;
use Utils\JWTHandler;
use Utils\Response;

class OrderController {
    private $db;
    private $orderModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->orderModel = new Order($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function getMyOrders() {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            $orders = $this->orderModel->getByUser($user->id);
            
            Response::success(['orders' => $orders]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            $order = $this->orderModel->getById($id);
            
            if (!$order) {
                Response::notFound('Order not found');
            }
            
            // Check if user owns the order
            if ($order['user_id'] !== $user->id) {
                Response::forbidden('You can only view your own orders');
            }
            
            Response::success(['order' => $order]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
}
