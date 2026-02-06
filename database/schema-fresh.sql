-- Event Ticketing System Database Schema
-- DROP AND RECREATE VERSION (Use this if database already exists)

-- Drop existing database if it exists
DROP DATABASE IF EXISTS event_ticketing;

-- Create database
CREATE DATABASE event_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_ticketing;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('attendee', 'organizer') DEFAULT 'attendee',
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    venue VARCHAR(255) NOT NULL,
    address TEXT,
    event_date DATETIME NOT NULL,
    end_date DATETIME,
    image_url VARCHAR(500),
    status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_organizer (organizer_id),
    INDEX idx_event_date (event_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket types table
CREATE TABLE ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    quantity_sold INT DEFAULT 0,
    sale_start_date DATETIME,
    sale_end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    CHECK (quantity_sold <= quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_event (event_id),
    INDEX idx_order_number (order_number),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tickets table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    qr_code VARCHAR(255) NOT NULL,
    status ENUM('valid', 'used', 'cancelled', 'refunded') DEFAULT 'valid',
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_user (user_id),
    INDEX idx_event (event_id),
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_qr_code (qr_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Sample users with working passwords
-- Password for both users: 'password'
INSERT INTO users (email, password_hash, first_name, last_name, role, phone) VALUES
('organizer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Organizer', 'organizer', '+1234567890'),
('attendee@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Attendee', 'attendee', '+0987654321');

-- Sample events (multiple events added)
INSERT INTO events (organizer_id, title, description, venue, address, event_date, end_date, image_url, status) VALUES
(1, 'Tech Conference 2026', 'Annual technology conference featuring the latest innovations and networking opportunities. Join us for inspiring talks, workshops, and networking sessions.', 'Convention Center', '123 Main Street, Tech City, TC 12345', '2026-03-15 09:00:00', '2026-03-15 18:00:00', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800', 'published'),

(1, 'Summer Music Festival', 'Three-day outdoor music festival featuring top international artists, food trucks, and camping. Experience the best summer vibes!', 'City Park Amphitheater', '456 Park Avenue, Music City, MC 54321', '2026-07-20 14:00:00', '2026-07-22 23:00:00', 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800', 'published'),

(1, 'Business Networking Gala', 'An exclusive evening of networking with industry leaders, entrepreneurs, and investors. Includes dinner and keynote speeches.', 'Grand Hotel Ballroom', '789 Business District, Metro City, MC 67890', '2026-05-10 18:00:00', '2026-05-10 22:00:00', 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800', 'published'),

(1, 'Food & Wine Expo 2026', 'Taste the finest cuisines from around the world. Wine tasting, cooking demonstrations, and celebrity chef appearances.', 'Exhibition Center', '321 Culinary Lane, Food Town, FT 13579', '2026-06-05 11:00:00', '2026-06-07 20:00:00', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800', 'published'),

(1, 'Marathon Challenge 2026', 'Annual city marathon with 5K, 10K, half marathon, and full marathon categories. All fitness levels welcome!', 'City Square', 'Downtown Start Point, Runner City, RC 24680', '2026-04-18 06:00:00', '2026-04-18 14:00:00', 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800', 'published'),

(1, 'Art Exhibition: Modern Masters', 'A curated exhibition featuring contemporary art from emerging and established artists. Gallery opening with wine reception.', 'Modern Art Gallery', '555 Art District, Culture City, CC 11223', '2026-08-12 17:00:00', '2026-08-12 21:00:00', 'https://images.unsplash.com/photo-1531243269054-5ebf6f34081e?w=800', 'published');

-- Sample ticket types for all events
-- Tech Conference
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(1, 'General Admission', 'Access to all conference sessions and networking areas', 99.00, 500),
(1, 'VIP Pass', 'Premium seating, exclusive networking lounge, and conference materials', 299.00, 100),
(1, 'Student Pass', 'Discounted access for students with valid ID', 49.00, 200);

-- Summer Music Festival
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(2, 'General Admission', '3-day festival pass with camping access', 199.00, 5000),
(2, 'VIP Weekend Pass', 'Premium viewing areas, VIP lounge, and exclusive meet & greets', 599.00, 500),
(2, 'Single Day Pass', 'Access for one day of your choice', 79.00, 2000);

-- Business Networking Gala
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(3, 'Standard Ticket', 'Entry, dinner, and networking access', 150.00, 300),
(3, 'Premium Table', 'Reserved table for 8 with premium seating', 1500.00, 30);

-- Food & Wine Expo
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(4, 'Day Pass', 'Single day entry with 10 tasting tokens', 45.00, 1000),
(4, 'Weekend Pass', 'All 3 days with unlimited tastings', 120.00, 500),
(4, 'VIP Experience', 'All access pass with chef meet & greets and reserved seating', 250.00, 100);

-- Marathon Challenge
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(5, '5K Fun Run', 'Entry for 5K category with race kit', 25.00, 2000),
(5, 'Half Marathon', 'Entry for half marathon with race kit and medal', 55.00, 1000),
(5, 'Full Marathon', 'Entry for full marathon with race kit, medal, and finisher certificate', 75.00, 800);

-- Art Exhibition
INSERT INTO ticket_types (event_id, name, description, price, quantity) VALUES
(6, 'General Admission', 'Gallery entry with wine reception', 35.00, 400),
(6, 'Patron Ticket', 'Gallery entry, wine reception, and artist meet & greet', 100.00, 100);
