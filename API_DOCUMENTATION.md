# API Documentation - Event Ticketing System

Base URL: `http://yourdomain.com/api`

All endpoints return JSON responses in the following format:

```json
{
  "success": true/false,
  "message": "Response message",
  "data": { ... },
  "errors": { ... }  // Only on validation errors
}
```

## Authentication

Most endpoints require JWT token authentication. Include the token in the Authorization header:

```
Authorization: Bearer your_jwt_token_here
```

---

## Auth Endpoints

### Register User

**POST** `/auth/register`

Create a new user account.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890",
  "role": "attendee"  // or "organizer"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "jwt_token_here",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "role": "attendee",
      "created_at": "2026-01-21 10:00:00"
    }
  }
}
```

### Login

**POST** `/auth/login`

Authenticate a user.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "jwt_token_here",
    "user": { ... }
  }
}
```

### Get Current User

**GET** `/auth/me`

Get currently authenticated user details.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "first_name": "John",
      "last_name": "Doe",
      "role": "attendee"
    }
  }
}
```

---

## Event Endpoints

### Get All Events

**GET** `/events`

Get list of all published events.

**Query Parameters:**
- `status` - Filter by status (published, draft, cancelled)
- `upcoming` - true to get only future events
- `search` - Search in title, description, venue

**Example:** `/events?status=published&upcoming=true&search=concert`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 1,
        "title": "Tech Conference 2026",
        "description": "Annual technology conference...",
        "venue": "Convention Center",
        "address": "123 Main Street",
        "event_date": "2026-03-15 09:00:00",
        "end_date": "2026-03-15 18:00:00",
        "image_url": "https://...",
        "status": "published",
        "organizer_id": 1,
        "first_name": "John",
        "last_name": "Organizer",
        "tickets_sold": 50,
        "ticket_types": [
          {
            "id": 1,
            "name": "General Admission",
            "price": "99.00",
            "quantity": 500,
            "quantity_sold": 30
          }
        ]
      }
    ]
  }
}
```

### Get Event by ID

**GET** `/events/{id}`

Get detailed information about a specific event.

**Response:** `200 OK` (same structure as above, single event)

### Create Event

**POST** `/events`

Create a new event. Requires organizer role.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "title": "Summer Music Festival",
  "description": "3-day outdoor music festival",
  "venue": "City Park",
  "address": "456 Park Avenue",
  "event_date": "2026-07-15 12:00:00",
  "end_date": "2026-07-17 23:00:00",
  "image_url": "https://example.com/image.jpg",
  "status": "published",
  "ticket_types": [
    {
      "name": "General Admission",
      "description": "3-day pass",
      "price": 150.00,
      "quantity": 1000
    },
    {
      "name": "VIP Pass",
      "description": "All access pass",
      "price": 500.00,
      "quantity": 100
    }
  ]
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Event created successfully",
  "data": {
    "event": { ... }
  }
}
```

### Update Event

**PUT** `/events/{id}`

Update an existing event. Only event organizer can update.

**Headers:** `Authorization: Bearer {token}`

**Request Body:** (same as create, all fields optional)

**Response:** `200 OK`

### Delete Event

**DELETE** `/events/{id}`

Delete an event. Only event organizer can delete.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Event deleted successfully",
  "data": []
}
```

---

## Ticket Endpoints

### Purchase Tickets

**POST** `/tickets`

Purchase tickets for an event.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "event_id": 1,
  "payment_method": "simulated",
  "tickets": [
    {
      "ticket_type_id": 1,
      "quantity": 2
    },
    {
      "ticket_type_id": 2,
      "quantity": 1
    }
  ]
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Purchase successful",
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD20260121ABCD1234",
      "total_amount": "348.00",
      "payment_status": "completed",
      "tickets": [
        {
          "id": 1,
          "ticket_number": "TKT20260121ABCDEF1234",
          "ticket_type_name": "General Admission",
          "price": "99.00",
          "status": "valid",
          "qr_code": "qrcodes/TKT20260121ABCDEF1234.png"
        }
      ]
    }
  }
}
```

### Get My Tickets

**GET** `/tickets/my-tickets`

Get all tickets for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": 1,
        "ticket_number": "TKT20260121ABCDEF1234",
        "event_title": "Tech Conference 2026",
        "event_date": "2026-03-15 09:00:00",
        "venue": "Convention Center",
        "ticket_type_name": "General Admission",
        "price": "99.00",
        "status": "valid",
        "qr_code": "qrcodes/TKT20260121ABCDEF1234.png",
        "order_number": "ORD20260121ABCD1234"
      }
    ]
  }
}
```

### Get Ticket by ID

**GET** `/tickets/{id}`

Get details of a specific ticket.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

---

## Order Endpoints

### Get My Orders

**GET** `/orders/my-orders`

Get all orders for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": 1,
        "order_number": "ORD20260121ABCD1234",
        "event_title": "Tech Conference 2026",
        "event_date": "2026-03-15 09:00:00",
        "venue": "Convention Center",
        "total_amount": "348.00",
        "payment_status": "completed",
        "payment_method": "simulated",
        "ticket_count": 3,
        "created_at": "2026-01-21 10:30:00"
      }
    ]
  }
}
```

### Get Order by ID

**GET** `/orders/{id}`

Get details of a specific order.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`

---

## Organizer Endpoints

### Get My Events

**GET** `/organizer/events`

Get all events created by authenticated organizer.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 1,
        "title": "Tech Conference 2026",
        "event_date": "2026-03-15 09:00:00",
        "status": "published",
        "tickets_sold": 50,
        "total_revenue": "4950.00"
      }
    ]
  }
}
```

### Get Event Statistics

**GET** `/organizer/stats/{eventId}`

Get detailed statistics for a specific event.

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "stats": {
      "event": { ... },
      "ticket_stats": [
        {
          "ticket_type": "General Admission",
          "price": "99.00",
          "total_available": 500,
          "quantity_sold": 30,
          "remaining": 470,
          "revenue": "2970.00"
        }
      ],
      "summary": {
        "total_revenue": "4950.00",
        "total_sold": 50,
        "total_available": 600,
        "remaining": 550
      }
    }
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Email is required",
    "password": "Password must be at least 6 characters"
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Only organizers can create events"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## Rate Limiting

Currently not implemented. Consider adding rate limiting in production.

## Testing

Use tools like Postman, Insomnia, or curl to test the API.

Example curl request:
```bash
curl -X POST https://yourdomain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"attendee@example.com","password":"password"}'
```
