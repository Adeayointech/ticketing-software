<?php
namespace Controllers;

use Config\Database;
use Models\Event;
use Models\Ticket;
use Utils\JWTHandler;
use Utils\Response;

class OrganizerController {
    private $db;
    private $eventModel;
    private $ticketModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->eventModel = new Event($this->db);
        $this->ticketModel = new Ticket($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function getMyEvents() {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            
            if ($user->role !== 'organizer') {
                Response::forbidden('Only organizers can access this endpoint');
            }
            
            $events = $this->eventModel->getByOrganizer($user->id);
            
            Response::success(['events' => $events]);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    public function getStats($eventId) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            
            if ($user->role !== 'organizer') {
                Response::forbidden('Only organizers can access this endpoint');
            }
            
            // Get event
            $event = $this->eventModel->getById($eventId);
            
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            // Check if user owns the event
            if ($event['organizer_id'] !== $user->id) {
                Response::forbidden('You can only view stats for your own events');
            }
            
            // Get ticket sales statistics
            $query = "SELECT 
                        tt.name as ticket_type,
                        tt.price,
                        tt.quantity as total_available,
                        tt.quantity_sold,
                        (tt.quantity - tt.quantity_sold) as remaining,
                        (tt.price * tt.quantity_sold) as revenue
                      FROM ticket_types tt
                      WHERE tt.event_id = :event_id
                      ORDER BY tt.price DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            $ticketStats = $stmt->fetchAll();
            
            // Calculate totals
            $totalRevenue = 0;
            $totalSold = 0;
            $totalAvailable = 0;
            
            foreach ($ticketStats as $stat) {
                $totalRevenue += $stat['revenue'];
                $totalSold += $stat['quantity_sold'];
                $totalAvailable += $stat['total_available'];
            }
            
            $stats = [
                'event' => $event,
                'ticket_stats' => $ticketStats,
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_sold' => $totalSold,
                    'total_available' => $totalAvailable,
                    'remaining' => $totalAvailable - $totalSold
                ]
            ];
            
            Response::success(['stats' => $stats]);
            
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
}
