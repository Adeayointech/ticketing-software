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
                $ticketIds = [];
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
                            'qr_code' => 'qrcodes/placeholder.png', // placeholder, will update after transaction
                            'status' => 'valid'
                        ];
                        
                        // Debug logging
                        error_log("Creating ticket with data: " . json_encode($newTicket));
                        
                        $ticketId = $this->ticketModel->create($newTicket);
                        if (!$ticketId) {
                            error_log("Failed to create ticket for order $orderId");
                            throw new \Exception('Failed to create ticket');
                        }
                        
                        error_log("Created ticket ID: $ticketId for user " . $user->id . " event " . $data['event_id']);
                        
                        // Store ticket ID for QR generation after transaction
                        $ticketIds[] = $ticketId;
                    }
                }
                
                // Commit transaction BEFORE QR generation
                $this->db->commit();
                
                // Generate QR codes AFTER transaction (so tickets are saved even if QR fails)
                $tickets = [];
                foreach ($ticketIds as $ticketId) {
                    try {
                        $qrCode = $this->generateQRCode($ticketId);
                        $this->ticketModel->updateQRCode($ticketId, $qrCode);
                        error_log("QR code generated for ticket $ticketId: $qrCode");
                    } catch (\Exception $qrError) {
                        error_log("QR generation failed for ticket $ticketId: " . $qrError->getMessage());
                        // Continue anyway - ticket is already saved
                    }
                    $tickets[] = $this->ticketModel->getById($ticketId);
                }
                
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
            
            // Debug: Log the user ID
            error_log("Fetching tickets for user ID: " . $user->id);
            
            $tickets = $this->ticketModel->getByUser($user->id);
            
            // Debug: Log ticket count
            error_log("Found " . count($tickets) . " tickets");
            
            // Ensure each ticket has required fields
            foreach ($tickets as &$ticket) {
                if (empty($ticket['event_image'])) {
                    $ticket['event_image'] = null;
                }
                // Make sure all required fields exist
                $ticket['id'] = $ticket['id'] ?? null;
                $ticket['ticket_number'] = $ticket['ticket_number'] ?? '';
                $ticket['status'] = $ticket['status'] ?? 'valid';
                $ticket['qr_code'] = $ticket['qr_code'] ?? '';
            }
            
            Response::success(['tickets' => $tickets]);
        } catch (\Exception $e) {
            error_log("Error in getMyTickets: " . $e->getMessage());
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
    
    private function generateQRCode($ticketId) {
        try {
            // Use the full phpqrcode library
            require_once __DIR__ . '/../utils/phpqrcode/qrlib.php';
            
            $qrCodePath = QR_CODE_DIR . $ticketId . '.png';
            
            // Ensure directory exists
            if (!file_exists(QR_CODE_DIR)) {
                mkdir(QR_CODE_DIR, 0777, true);
            }
            
            // Generate QR code using the full library
            QRcode::png($ticketId, $qrCodePath, QR_ECLEVEL_L, QR_CODE_SIZE, QR_CODE_MARGIN);
            
            error_log("QR code generated successfully for ticket $ticketId");
            return 'qrcodes/' . $ticketId . '.png';
        } catch (\Exception $e) {
            error_log("QR code generation failed: " . $e->getMessage());
            // Return placeholder if QR generation fails
            return 'qrcodes/placeholder.png';
        }
    }
}
