<?php
namespace Controllers;

use Config\Database;
use Models\Event;
use Models\TicketType;
use Utils\JWTHandler;
use Utils\Response;

class EventController {
    private $db;
    private $eventModel;
    private $ticketTypeModel;
    private $jwtHandler;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->eventModel = new Event($this->db);
        $this->ticketTypeModel = new TicketType($this->db);
        $this->jwtHandler = new JWTHandler();
    }
    
    public function getAll($filters = []) {
        // Public endpoint - no auth required
        $events = $this->eventModel->getAll($filters);
        
        // Get ticket types for each event
        foreach ($events as &$event) {
            $event['ticket_types'] = $this->ticketTypeModel->getByEvent($event['id']);
        }
        
        Response::success(['events' => $events]);
    }
    
    public function getById($id) {
        // Public endpoint - no auth required
        $event = $this->eventModel->getById($id);
        
        if (!$event) {
            Response::notFound('Event not found');
        }
        
        // Get ticket types
        $event['ticket_types'] = $this->ticketTypeModel->getByEvent($id);
        
        Response::success(['event' => $event]);
    }
    
    public function create($data) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            
            // Check if user is organizer
            if ($user->role !== 'organizer') {
                Response::forbidden('Only organizers can create events');
            }
            
            // Validate input
            $errors = [];
            
            if (empty($data['title'])) {
                $errors['title'] = 'Title is required';
            }
            
            if (empty($data['venue'])) {
                $errors['venue'] = 'Venue is required';
            }
            
            if (empty($data['event_date'])) {
                $errors['event_date'] = 'Event date is required';
            }
            
            if (!empty($errors)) {
                Response::error('Validation failed', 422, $errors);
            }
            
            // Create event
            $eventData = [
                'organizer_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'venue' => $data['venue'],
                'address' => $data['address'] ?? null,
                'event_date' => $data['event_date'],
                'end_date' => $data['end_date'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'status' => $data['status'] ?? 'published'
            ];
            
            $eventId = $this->eventModel->create($eventData);
            
            if (!$eventId) {
                Response::error('Failed to create event', 500);
            }
            
            // Create ticket types
            if (isset($data['ticket_types']) && is_array($data['ticket_types'])) {
                foreach ($data['ticket_types'] as $ticketType) {
                    $ticketTypeData = [
                        'event_id' => $eventId,
                        'name' => $ticketType['name'],
                        'description' => $ticketType['description'] ?? null,
                        'price' => $ticketType['price'],
                        'quantity' => $ticketType['quantity'],
                        'sale_start_date' => $ticketType['sale_start_date'] ?? null,
                        'sale_end_date' => $ticketType['sale_end_date'] ?? null
                    ];
                    
                    $this->ticketTypeModel->create($ticketTypeData);
                }
            }
            
            $event = $this->eventModel->getById($eventId);
            $event['ticket_types'] = $this->ticketTypeModel->getByEvent($eventId);
            
            Response::success(['event' => $event], 'Event created successfully', 201);
            
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    public function update($id, $data) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            
            // Get event
            $event = $this->eventModel->getById($id);
            
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            // Check if user is the organizer
            if ($event['organizer_id'] !== $user->id) {
                Response::forbidden('You can only update your own events');
            }
            
            // Update event
            $eventData = [
                'title' => $data['title'] ?? $event['title'],
                'description' => $data['description'] ?? $event['description'],
                'venue' => $data['venue'] ?? $event['venue'],
                'address' => $data['address'] ?? $event['address'],
                'event_date' => $data['event_date'] ?? $event['event_date'],
                'end_date' => $data['end_date'] ?? $event['end_date'],
                'image_url' => $data['image_url'] ?? $event['image_url'],
                'status' => $data['status'] ?? $event['status']
            ];
            
            if (!$this->eventModel->update($id, $eventData)) {
                Response::error('Failed to update event', 500);
            }
            
            $updatedEvent = $this->eventModel->getById($id);
            $updatedEvent['ticket_types'] = $this->ticketTypeModel->getByEvent($id);
            
            Response::success(['event' => $updatedEvent], 'Event updated successfully');
            
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    public function delete($id) {
        try {
            $user = $this->jwtHandler->getCurrentUser();
            
            // Get event
            $event = $this->eventModel->getById($id);
            
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            // Check if user is the organizer
            if ($event['organizer_id'] !== $user->id) {
                Response::forbidden('You can only delete your own events');
            }
            
            if (!$this->eventModel->delete($id)) {
                Response::error('Failed to delete event', 500);
            }
            
            Response::success([], 'Event deleted successfully');
            
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
}
