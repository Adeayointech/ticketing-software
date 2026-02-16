
<?php
// Load config first to get environment variables
require_once __DIR__ . '/config/config.php';

// Set CORS headers - Use wildcard for testing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit();
}

require_once __DIR__ . '/autoload.php';

// Parse the request
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($script_name, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];
$segments = explode('/', $path);

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $input = [];
}

// DEBUG: Output raw URI and parsed path/segments if debug=1
if (isset($_GET['debug'])) {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $path = str_replace($script_name, '', $request_uri);
    $path = parse_url($path, PHP_URL_PATH);
    $path = trim($path, '/');
    $segments = explode('/', $path);
    echo json_encode([
        'REQUEST_URI' => $request_uri,
        'SCRIPT_NAME' => $script_name,
        'path' => $path,
        'segments' => $segments,
    ]);
    exit();
}

// Router
try {
    // Remove 'api' from segments if present
    if ($segments[0] === 'api') {
        array_shift($segments);
    }
    
    $resource = $segments[0] ?? '';
    $action = $segments[1] ?? '';
    $id = null;
    
    // If second segment is numeric, treat it as ID, otherwise as action
    if ($action && is_numeric($action)) {
        $id = $action;
        $action = '';
    } elseif (isset($segments[2]) && is_numeric($segments[2])) {
        $id = $segments[2];
    }

    // TEMPORARY DEBUG: Log routing info
    error_log("DEBUG - Resource: $resource, Action: $action, Method: $method, Segments: " . json_encode($segments));

    switch ($resource) {
        case 'auth':
            $controller = new Controllers\AuthController();
            if ($method === 'POST' && $action === 'register') {
                $controller->register($input);
            } elseif ($method === 'POST' && $action === 'login') {
                $controller->login($input);
            } elseif ($method === 'GET' && $action === 'me') {
                $controller->getCurrentUser();
            } else {
                throw new Exception('Invalid auth endpoint');
            }
            break;
            
        case 'events':
            $controller = new Controllers\EventController();
            if ($method === 'GET' && !$id) {
                $controller->getAll($_GET);
            } elseif ($method === 'GET' && $id) {
                $controller->getById($id);
            } elseif ($method === 'POST') {
                $controller->create($input);
            } elseif ($method === 'PUT' && $id) {
                $controller->update($id, $input);
            } elseif ($method === 'DELETE' && $id) {
                $controller->delete($id);
            } else {
                throw new Exception('Invalid events endpoint');
            }
            break;
            
        case 'tickets':
            $controller = new Controllers\TicketController();
            if ($method === 'GET' && $action === 'my-tickets') {
                $controller->getMyTickets();
            } elseif ($method === 'GET' && $id) {
                $controller->getById($id);
            } elseif ($method === 'POST' && $action === 'validate') {
                $controller->validateTicket($input);
            } elseif ($method === 'POST') {
                $controller->purchase($input);
            } else {
                throw new Exception('Invalid tickets endpoint');
            }
            break;
            
        case 'orders':
            $controller = new Controllers\OrderController();
            if ($method === 'GET' && $action === 'my-orders') {
                $controller->getMyOrders();
            } elseif ($method === 'GET' && $id) {
                $controller->getById($id);
            } else {
                throw new Exception('Invalid orders endpoint');
            }
            break;
            
        case 'organizer':
            $controller = new Controllers\OrganizerController();
            if ($action === 'events' && $method === 'GET') {
                $controller->getMyEvents();
            } elseif ($action === 'stats' && $method === 'GET') {
                $controller->getStats($id);
            } else {
                throw new Exception('Invalid organizer endpoint');
            }
            break;
            
        case 'users':
            $controller = new Controllers\UserController();
            if ($action === 'profile' && $method === 'PUT') {
                $controller->updateProfile($input);
            } else {
                throw new Exception('Invalid users endpoint');
            }
            break;
            
        default:
            throw new Exception('Resource not found');
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
