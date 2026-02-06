# Project Structure

## Backend Structure

```
backend/
├── .htaccess                   # Apache URL rewriting & CORS
├── .env.example                # Environment variables template
├── .gitignore                  # Git ignore file
├── index.php                   # Main API entry point & router
├── autoload.php                # PSR-4 autoloader
│
├── config/
│   ├── Database.php            # Database connection class
│   └── config.php              # Application configuration
│
├── controllers/
│   ├── AuthController.php      # Authentication endpoints
│   ├── EventController.php     # Event CRUD operations
│   ├── TicketController.php    # Ticket purchase & management
│   ├── OrderController.php     # Order management
│   └── OrganizerController.php # Organizer-specific operations
│
├── models/
│   ├── User.php                # User database model
│   ├── Event.php               # Event database model
│   ├── TicketType.php          # Ticket type database model
│   ├── Order.php               # Order database model
│   └── Ticket.php              # Ticket database model
│
├── utils/
│   ├── JWTHandler.php          # JWT token generation & validation
│   └── Response.php            # Standardized API responses
│
├── Firebase/
│   └── JWT/
│       └── JWT.php             # JWT library implementation
│
└── uploads/
    └── qrcodes/                # Generated QR code images
```

## Frontend Structure

```
frontend/
├── public/
│   ├── index.html              # HTML template
│   └── favicon.ico             # Site icon
│
├── src/
│   ├── index.js                # React entry point
│   ├── index.css               # Global styles
│   ├── App.js                  # Main App component with routing
│   ├── App.css                 # App-level styles
│   │
│   ├── components/
│   │   ├── Navbar.js           # Navigation bar component
│   │   ├── Navbar.css
│   │   ├── EventCard.js        # Event display card
│   │   └── EventCard.css
│   │
│   ├── contexts/
│   │   └── AuthContext.js      # Authentication context & provider
│   │
│   ├── services/
│   │   └── api.js              # Axios API service & endpoints
│   │
│   └── pages/
│       ├── Home.js             # Homepage with event listing
│       ├── Home.css
│       ├── Login.js            # Login page
│       ├── Register.js         # Registration page
│       ├── Auth.css            # Auth pages styles
│       ├── EventDetails.js     # Event details & ticket purchase
│       ├── EventDetails.css
│       ├── MyTickets.js        # User's tickets
│       ├── MyTickets.css
│       ├── MyOrders.js         # User's order history
│       ├── MyOrders.css
│       ├── Dashboard.js        # User dashboard
│       ├── Dashboard.css
│       ├── CreateEvent.js      # Create event form (organizer)
│       ├── CreateEvent.css
│       ├── OrganizerDashboard.js # Organizer's events
│       └── OrganizerDashboard.css
│
├── .env.example                # Environment variables template
├── .gitignore                  # Git ignore file
└── package.json                # npm dependencies & scripts
```

## Database Structure

```
database/
└── schema.sql                  # Complete database schema with sample data

Tables:
  - users                       # User accounts
  - events                      # Event information
  - ticket_types                # Ticket types for events
  - orders                      # Purchase orders
  - tickets                     # Individual tickets
```

## Root Files

```
/
├── .github/
│   └── copilot-instructions.md # GitHub Copilot instructions
├── README.md                   # Main project documentation
├── DEPLOYMENT.md               # Deployment guide
└── API_DOCUMENTATION.md        # API reference
```

## Key Technologies

### Backend
- **PHP 7.4+**: Server-side language
- **MySQL**: Relational database
- **PDO**: Database access layer
- **JWT**: Token-based authentication
- **Apache/htaccess**: URL routing

### Frontend
- **React 18**: UI library
- **React Router 6**: Client-side routing
- **Axios**: HTTP client
- **Context API**: State management
- **CSS3**: Styling

## Design Patterns

### Backend
- **MVC Pattern**: Models, Controllers separated
- **RESTful API**: Standard HTTP methods
- **Repository Pattern**: Database access via models
- **Dependency Injection**: Database connection injected

### Frontend
- **Component-Based**: Reusable UI components
- **Context Pattern**: Global state management
- **Service Layer**: API calls abstracted
- **Protected Routes**: Authentication guards

## Data Flow

### Authentication Flow
1. User submits credentials → AuthController
2. Controller validates → User model
3. Generate JWT token → JWTHandler
4. Return token to frontend
5. Store token in localStorage
6. Include token in subsequent requests

### Ticket Purchase Flow
1. User selects tickets → EventDetails page
2. Submit purchase → TicketController
3. Begin database transaction
4. Check availability → TicketType model
5. Create order → Order model
6. Generate tickets → Ticket model
7. Commit transaction
8. Return tickets with QR codes

### Event Creation Flow
1. Organizer fills form → CreateEvent page
2. Submit event data → EventController
3. Validate organizer role → JWTHandler
4. Create event → Event model
5. Create ticket types → TicketType model
6. Return created event

## Security Layers

1. **Authentication**: JWT tokens
2. **Authorization**: Role-based access
3. **SQL Injection**: Prepared statements
4. **XSS Prevention**: Output encoding
5. **CORS**: Configured allowed origins
6. **Password Security**: Bcrypt hashing

## File Upload Structure

```
uploads/
├── events/                     # Event images (future)
└── qrcodes/                    # Generated QR codes
    └── TKT20260121ABCD.png
```

## Configuration Files

### Backend
- `.env` - Database & JWT configuration
- `config/config.php` - Application settings
- `.htaccess` - Apache configuration

### Frontend
- `.env` - API URL configuration
- `package.json` - Dependencies & scripts

## Build & Deployment

### Development
```bash
# Backend: No build needed, runs on PHP
# Frontend: npm start (development server)
```

### Production
```bash
# Backend: Upload PHP files directly
# Frontend: npm run build → upload /build contents
```
