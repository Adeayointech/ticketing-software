    // Validate and mark ticket as used (for scanning)
    public function validateTicket($data) {
        try {
            $debug = [];
            try {
                $user = $this->jwtHandler->getCurrentUser();
                $debug['user'] = $user;
            } catch (\Exception $jwtEx) {
                $debug['jwt_error'] = $jwtEx->getMessage();
                Response::error('JWT error', 401, $debug);
            }
            if (empty($data['ticket_id']) && empty($data['ticket_number'])) {
                $debug['input'] = $data;
                Response::error('Ticket ID or Ticket Number is required', 422, $debug);
            }
            $ticket = null;
            $debug['received_ticket_id'] = $data['ticket_id'] ?? null;
            $debug['received_ticket_number'] = $data['ticket_number'] ?? null;
            if (!empty($data['ticket_id'])) {
                $ticket = $this->ticketModel->getById($data['ticket_id']);
            } elseif (!empty($data['ticket_number'])) {
                $ticket = $this->ticketModel->getByTicketNumber($data['ticket_number']);
            }
            $debug['fetched_ticket'] = $ticket;
            if (!$ticket) {
                Response::error('Ticket not found', 404, $debug);
            }
            if ($ticket['status'] === 'used') {
                $debug['status'] = $ticket['status'];
                Response::error('Ticket already used', 409, $debug);
            }
            if ($ticket['status'] !== 'valid') {
                $debug['status'] = $ticket['status'];
                Response::error('Ticket is not valid', 400, $debug);
            }
            // Mark as used
            $this->ticketModel->updateStatus($ticket['id'], 'used');
            $debug['final'] = 'Ticket marked as used';
            Response::success(['ticket' => $ticket, 'message' => 'Ticket is valid and now marked as used.', 'debug' => $debug]);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
<?php
namespace Controllers;

use Config\Database;
use Models\Ticket;
use Models\Order;
use Models\TicketType;
use Models\Event;
use Utils\JWTHandler;
use Utils\Response;

class TicketController {
    private $db;
    private $ticketModel;
    private $orderModel;
    private $ticketTypeModel;
    private $eventModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ticketModel = new Ticket($this->db);
        $this->orderModel = new Order($this->db);
        $this->ticketTypeModel = new TicketType($this->db);
        $this->eventModel = new Event($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function purchase($data) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            // Only attendees can purchase tickets
            if (!isset($user->role) || strtolower($user->role) !== 'attendee') {
                Response::error('Only attendees can purchase tickets.', 403);
            }
            // Validate input
            $errors = [];
            if (empty($data['event_id'])) {
                $errors['event_id'] = 'Event ID is required';
            }
            if (empty($data['tickets']) || !is_array($data['tickets'])) {
                $errors['tickets'] = 'Tickets array is required';
            }
            if (!empty($errors)) {
                Response::error('Validation failed', 422, $errors);
            }
            // Get event
            $event = $this->eventModel->getById($data['event_id']);
            if (!$event) {
                Response::notFound('Event not found');
            }
            // Start transaction
            $this->db->beginTransaction();
            try {
                $totalAmount = 0;
                $ticketData = [];
                
                // Process each ticket type
                foreach ($data['tickets'] as $ticketRequest) {
                    $ticketTypeId = $ticketRequest['ticket_type_id'];
                    $quantity = $ticketRequest['quantity'];
                    
                    // Get ticket type
                    $ticketType = $this->ticketTypeModel->getById($ticketTypeId);
                    if (!$ticketType) {
                        throw new \Exception('Ticket type not found');
                    }
                    
                    // Check availability
                    if (!$this->ticketTypeModel->checkAvailability($ticketTypeId, $quantity)) {
                        throw new \Exception('Not enough tickets available for ' . $ticketType['name']);
                    }
                    
                    // Update quantity sold
                    if (!$this->ticketTypeModel->updateQuantitySold($ticketTypeId, $quantity)) {
                        throw new \Exception('Failed to reserve tickets');
                    }
                    
                    // Calculate amount
                    $totalAmount += $ticketType['price'] * $quantity;
                    
                    // Store ticket data
                    $ticketData[] = [
                        'ticket_type_id' => $ticketTypeId,
                        'quantity' => $quantity,
                        'price' => $ticketType['price']
                    ];
                }
                
                // Create order
                $orderData = [
                    'user_id' => $user->id,
                    'event_id' => $data['event_id'],
                    'order_number' => $this->orderModel->generateOrderNumber(),
                    'total_amount' => $totalAmount,
                    'payment_status' => 'completed', // Simulated payment
                    'payment_method' => $data['payment_method'] ?? 'simulated',
                    'transaction_id' => 'TXN' . time() . rand(1000, 9999)
                ];
                
                $orderId = $this->orderModel->create($orderData);
                
                if (!$orderId) {
                    throw new \Exception('Failed to create order');
                }
                
                // Create tickets
                $tickets = [];
                foreach ($ticketData as $ticket) {
                    for ($i = 0; $i < $ticket['quantity']; $i++) {
                        $ticketNumber = $this->ticketModel->generateTicketNumber();
                        // Create ticket record first to get ticket ID
                        $newTicket = [
                            'order_id' => $orderId,
                            'user_id' => $user->id,
                            'event_id' => $data['event_id'],
                            'ticket_type_id' => $ticket['ticket_type_id'],
                            'ticket_number' => $ticketNumber,
                            'qr_code' => '', // placeholder, will update after QR code is generated
                            'status' => 'valid'
                        ];
                        $ticketId = $this->ticketModel->create($newTicket);
                        if (!$ticketId) {
                            throw new \Exception('Failed to create ticket');
                        }
                        // Generate QR code with ticket ID only
                        $qrCode = $this->generateQRCode($ticketId);
                        // Update ticket record with QR code filename
                        $this->ticketModel->updateQRCode($ticketId, $qrCode);
                        $tickets[] = $this->ticketModel->getById($ticketId);
                    }
                }
                
                // Commit transaction
                $this->db->commit();
                
                // Get complete order details
                $order = $this->orderModel->getById($orderId);
                $order['tickets'] = $tickets;
                
                Response::success([
                    'order' => $order,
                    'message' => 'Tickets purchased successfully!'
                ], 'Purchase successful', 201);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    public function getMyTickets() {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            $tickets = $this->ticketModel->getByUser($user->id);
            
            Response::success(['tickets' => $tickets]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    public function getById($id) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            $ticket = $this->ticketModel->getById($id);
            
            if (!$ticket) {
                Response::notFound('Ticket not found');
            }
            
            // Check if user owns the ticket
            if ($ticket['user_id'] !== $user->id) {
                Response::forbidden('You can only view your own tickets');
            }
            
            Response::success(['ticket' => $ticket]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    private function generateQRCode($ticketNumber) {
        require_once __DIR__ . '/../utils/phpqrcode.php';
        $qrCodePath = QR_CODE_DIR . $ticketNumber . '.png';
        \QRcode::png($ticketNumber, $qrCodePath, QR_ECLEVEL_L, QR_CODE_SIZE, QR_CODE_MARGIN);
        return 'qrcodes/' . $ticketNumber . '.png';
    }
    private function generateQRCode($ticketId) {
        require_once __DIR__ . '/../utils/phpqrcode.php';
        $qrCodePath = QR_CODE_DIR . $ticketId . '.png';
        // Use the real phpqrcode library
        \QRcode::png($ticketId, $qrCodePath, QR_ECLEVEL_L, QR_CODE_SIZE, QR_CODE_MARGIN);
        return 'qrcodes/' . $ticketId . '.png';
    }
}
