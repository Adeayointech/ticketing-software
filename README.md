# Event Ticketing System

A complete full-stack event ticketing system designed for cPanel hosting. Built with React frontend and PHP REST API backend with MySQL database.

## 🚀 Features

### For Attendees
- Browse and search upcoming events
- View detailed event information
- Purchase tickets with multiple ticket types
- Receive digital tickets with QR codes
- View ticket history and orders
- User dashboard

### For Organizers
- Create and manage events
- Define multiple ticket types (General, VIP, etc.)
- Set pricing and quantity
- View sales statistics and revenue
- Track ticket sales per event
- Organizer dashboard

## 🛠️ Technology Stack

**Frontend:**
- React 18
- React Router 6
- Axios for API calls
- CSS3 for styling

**Backend:**
- PHP 7.4+
- RESTful API architecture
- JWT authentication
- MySQL database
- PDO for database operations

**Security:**
- JWT token-based authentication
- Prepared statements for SQL injection prevention
- Password hashing with bcrypt
- CORS configuration

## 📋 Prerequisites

- Node.js 14+ and npm (for development)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or cPanel hosting
- Composer (optional, JWT library included)

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd ticketing-software
```

### 2. Backend Setup

**Step 1: Configure Database**

1. Create a MySQL database:
```sql
CREATE DATABASE event_ticketing;
```

2. Import the database schema:
```bash
mysql -u your_username -p event_ticketing < database/schema.sql
```

**Step 2: Configure Environment**

1. Copy the environment file:
```bash
cd backend
copy .env.example .env
```

2. Edit `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_NAME=event_ticketing
DB_USER=your_database_user
DB_PASS=your_database_password

JWT_SECRET=your-secure-random-secret-key
```

**Step 3: Set Permissions**

Ensure the `uploads` directory is writable:
```bash
mkdir uploads
mkdir uploads/qrcodes
chmod 755 uploads
chmod 755 uploads/qrcodes
```

### 3. Frontend Setup

**Step 1: Install Dependencies**

```bash
cd frontend
npm install
```

**Step 2: Configure Environment**

1. Copy the environment file:
```bash
copy .env.example .env
```

2. Edit `.env` file:
```env
REACT_APP_API_URL=http://localhost/ticketing-backend
```

**Step 3: Development Mode**

Run the development server:
```bash
npm start
```

The app will open at `http://localhost:3000`

**Step 4: Production Build**

Build for production:
```bash
npm run build
```

This creates a `build` folder with optimized production files.

## 🌐 Deployment to cPanel

### Backend Deployment

1. **Upload Files:**
   - Upload the entire `backend` folder to your cPanel public_html directory (e.g., `public_html/api` or `public_html/backend`)

2. **Database Setup:**
   - Create MySQL database in cPanel
   - Import `database/schema.sql` using phpMyAdmin

3. **Configure Environment:**
   - Rename `.env.example` to `.env`
   - Update database credentials in `.env`
   - Generate a secure JWT_SECRET

4. **Set Permissions:**
   - Set `uploads` folder to 755
   - Ensure `.htaccess` file is present

### Frontend Deployment

1. **Build the Application:**
```bash
npm run build
```

2. **Upload to cPanel:**
   - Upload contents of `build` folder to `public_html`
   - Or create a subdirectory like `public_html/events`

3. **Configure API URL:**
   - Before building, update `.env` with your production API URL:
   ```env
   REACT_APP_API_URL=https://yourdomain.com/api
   ```

4. **Add .htaccess for React Router:**
   Create/update `.htaccess` in the frontend directory:
   ```apache
   <IfModule mod_rewrite.c>
     RewriteEngine On
     RewriteBase /
     RewriteRule ^index\.html$ - [L]
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteRule . /index.html [L]
   </IfModule>
   ```

## 📡 API Endpoints

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login user
- `GET /auth/me` - Get current user (requires auth)

### Events
- `GET /events` - Get all events
- `GET /events/{id}` - Get event by ID
- `POST /events` - Create event (organizer only)
- `PUT /events/{id}` - Update event (organizer only)
- `DELETE /events/{id}` - Delete event (organizer only)

### Tickets
- `POST /tickets` - Purchase tickets (requires auth)
- `GET /tickets/my-tickets` - Get user's tickets (requires auth)
- `GET /tickets/{id}` - Get ticket by ID (requires auth)

### Orders
- `GET /orders/my-orders` - Get user's orders (requires auth)
- `GET /orders/{id}` - Get order by ID (requires auth)

### Organizer
- `GET /organizer/events` - Get organizer's events (requires auth)
- `GET /organizer/stats/{eventId}` - Get event statistics (requires auth)

## 🧪 Demo Credentials

The system comes with sample users:

**Attendee Account:**
- Email: `attendee@example.com`
- Password: `password`

**Organizer Account:**
- Email: `organizer@example.com`
- Password: `password`

## 📝 Database Schema

The system uses 5 main tables:

- `users` - User accounts and authentication
- `events` - Event information
- `ticket_types` - Ticket types for each event
- `orders` - Purchase orders
- `tickets` - Individual tickets with QR codes

See `database/schema.sql` for complete structure.

## 🎨 Customization

### Styling
- Frontend styles are in `frontend/src/*.css` files
- Global styles in `frontend/src/index.css`
- Component-specific styles in respective CSS files

### Colors
Main brand color is `#007bff` (blue). To change:
1. Update in `frontend/src/index.css`
2. Search and replace throughout CSS files

### Features
- Payment integration: Update `backend/controllers/TicketController.php`
- QR code generation: Implement in `TicketController::generateQRCode()`
- Email notifications: Add email service in backend

## 🔐 Security Considerations

- Change JWT_SECRET in production
- Use HTTPS in production
- Implement rate limiting
- Add CAPTCHA for registration
- Validate file uploads
- Implement proper QR code generation library

## 🐛 Troubleshooting

**CORS Issues:**
- Check `backend/.htaccess` CORS headers
- Update `ALLOWED_ORIGINS` in `backend/config/config.php`

**Database Connection Failed:**
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check user permissions

**API 404 Errors:**
- Verify `.htaccess` is present in backend
- Check mod_rewrite is enabled
- Verify API URL in frontend `.env`

**React Router 404 on Refresh:**
- Add `.htaccess` to frontend build directory
- Configure server to serve `index.html` for all routes

## 📄 License

This project is open-source and available for educational and commercial use.

## 👨‍💻 Support

For issues and questions, please open an issue in the repository.

## 🚀 Future Enhancements

- [ ] Real payment gateway integration (Stripe/PayPal)
- [ ] Email notifications
- [ ] SMS notifications
- [ ] QR code scanning app
- [ ] Advanced analytics dashboard
- [ ] Event categories and filtering
- [ ] Social media integration
- [ ] Promo codes and discounts
- [ ] Recurring events
- [ ] Multi-language support

---

Built with ❤️ for demonstrating full-stack development skills
