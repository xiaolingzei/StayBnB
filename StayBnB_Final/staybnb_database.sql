-- ===================================
-- StayBnB Database Schema
-- Complete database structure
-- ===================================

DROP DATABASE IF EXISTS staybnb_db;
CREATE DATABASE staybnb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staybnb_db;

-- ===================================
-- USERS TABLE
-- ===================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    address TEXT,
    verification_token VARCHAR(64),
    is_verified TINYINT(1) DEFAULT 0,
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ===================================
-- ADMINS TABLE
-- ===================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'staff') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- ===================================
-- HOTELS TABLE
-- ===================================
CREATE TABLE hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    description TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    star_rating DECIMAL(2,1) DEFAULT 3.0,
    price_per_night DECIMAL(10,2) NOT NULL,
    available_rooms INT DEFAULT 10,
    total_rooms INT DEFAULT 10,
    check_in_time TIME DEFAULT '14:00:00',
    check_out_time TIME DEFAULT '12:00:00',
    amenities TEXT,
    policies TEXT,
    featured TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (location),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price_per_night)
) ENGINE=InnoDB;

-- ===================================
-- HOTEL IMAGES TABLE
-- ===================================
CREATE TABLE hotel_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    caption VARCHAR(200),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_hotel_primary (hotel_id, is_primary)
) ENGINE=InnoDB;

-- ===================================
-- BOOKINGS TABLE
-- ===================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    guest_fullname VARCHAR(100) NOT NULL,
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20),
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    num_guests INT DEFAULT 1,
    num_nights INT NOT NULL,
    room_rate DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    special_requests TEXT,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_booking_ref (booking_ref),
    INDEX idx_user_id (user_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_dates (checkin_date, checkout_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ===================================
-- REVIEWS TABLE
-- ===================================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL CHECK (rating >= 1.0 AND rating <= 5.0),
    title VARCHAR(200),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE,
    INDEX idx_hotel_status (hotel_id, status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ===================================
-- ACTIVITY LOGS TABLE
-- ===================================
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ===================================
-- USER PREFERENCES TABLE (for recommendations)
-- ===================================
CREATE TABLE user_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preferred_location VARCHAR(100),
    preferred_price_min DECIMAL(10,2),
    preferred_price_max DECIMAL(10,2),
    preferred_amenities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB;

-- ===================================
-- INSERT SAMPLE DATA
-- ===================================

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, email, password_hash, role) VALUES
('admin', 'admin@staybnb.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIq8G9xqKW', 'super_admin');

-- Insert sample hotels
INSERT INTO hotels (name, location, address, description, phone, email, star_rating, price_per_night, available_rooms, total_rooms, amenities) VALUES
('Bataan White Corals Beach Resort', 'Morong', 'Sitio Alas-asin, Brgy. Nagbalayong, Morong, Bataan', 'A beautiful beach resort with white sand beaches and crystal clear waters. Perfect for family getaways and romantic escapes.', '0915-234-5678', 'info@whitecoralsbataan.com', 4.5, 3500.00, 15, 20, 'Free WiFi,Swimming Pool,Restaurant,Parking,Beach Access'),

('The Plaza Hotel Balanga', 'Balanga City', '123 Capitol Drive, Balanga City, Bataan', 'Modern hotel in the heart of Balanga City. Close to historical sites and government offices.', '0917-345-6789', 'reservations@plazahotelbalanga.com', 4.0, 2500.00, 25, 30, 'Free WiFi,Restaurant,Conference Room,Parking,24/7 Reception'),

('Teresita''s Hotel & Resort', 'Bagac', 'National Road, Bagac, Bataan', 'Comfortable accommodation with great amenities. Near Mt. Samat and other historical landmarks.', '0919-456-7890', 'teresitas@gmail.com', 3.5, 2000.00, 20, 25, 'Free WiFi,Swimming Pool,Restaurant,Parking'),

('The Miele Hotel', 'Balanga City', '456 Main Street, Balanga City, Bataan', 'Boutique hotel offering personalized service and comfortable rooms.', '0918-567-8901', 'info@mielehotel.ph', 4.2, 2800.00, 12, 15, 'Free WiFi,Restaurant,Gym,Parking'),

('Bataan Beach Resort', 'Morong', 'Brgy. Mabayo, Morong, Bataan', 'Affordable beach resort perfect for groups and families.', '0916-678-9012', 'bataan.beach@yahoo.com', 3.8, 2200.00, 18, 22, 'Free WiFi,Beach Access,Restaurant,Parking,Kayaking'),

('Peninsula Hotel Bataan', 'Balanga City', '789 Peninsula Avenue, Balanga City, Bataan', 'Premium hotel with excellent facilities and service.', '0917-789-0123', 'reservations@peninsulahotelbataan.com', 4.8, 4500.00, 10, 12, 'Free WiFi,Swimming Pool,Spa,Restaurant,Gym,Conference Room'),

('Las Casas Filipinas de Acuzar', 'Bagac', 'Brgy. Ibaba, Bagac, Bataan', 'Heritage resort featuring restored Spanish-Filipino colonial houses.', '0918-890-1234', 'info@lascasasfilipinas.com', 5.0, 6000.00, 8, 10, 'Free WiFi,Swimming Pool,Restaurant,Museum,Heritage Tours'),

('Sunset View Inn', 'Mariveles', 'Waterfront Road, Mariveles, Bataan', 'Budget-friendly inn with beautiful sunset views over Manila Bay.', '0915-901-2345', 'sunsetviewinn@gmail.com', 3.0, 1500.00, 15, 18, 'Free WiFi,Parking,Fan Rooms,Sea View');

-- Insert sample hotel images
INSERT INTO hotel_images (hotel_id, image_url, is_primary) VALUES
(1, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4', 1),
(2, 'https://images.unsplash.com/photo-1566073771259-6a8506099945', 1),
(3, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa', 1),
(4, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb', 1),
(5, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d', 1),
(6, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b', 1),
(7, 'https://images.unsplash.com/photo-1564501049412-61c2a3083791', 1),
(8, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304', 1);

-- Insert sample user (email: test@example.com, password: password123)
INSERT INTO users (fullname, email, phone, password_hash, status, is_verified) VALUES
('Juan Dela Cruz', 'test@example.com', '09171234567', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIq8G9xqKW', 'active', 1);

-- ===================================
-- VIEWS FOR EASIER QUERIES
-- ===================================

-- View for hotel listings with images
CREATE OR REPLACE VIEW v_hotels_list AS
SELECT 
    h.*,
    hi.image_url,
    COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
    COUNT(DISTINCT r.review_id) as review_count
FROM hotels h
LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.status = 'approved'
WHERE h.status = 'active'
GROUP BY h.hotel_id;

-- View for booking history with details
CREATE OR REPLACE VIEW v_booking_details AS
SELECT 
    b.*,
    h.name as hotel_name,
    h.location,
    h.address,
    hi.image_url,
    u.fullname as user_fullname,
    u.email as user_email
FROM bookings b
JOIN hotels h ON b.hotel_id = h.hotel_id
JOIN users u ON b.user_id = u.user_id
LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1;

-- ===================================
-- STORED PROCEDURES
-- ===================================

-- Procedure to check hotel availability
DELIMITER //
CREATE PROCEDURE check_hotel_availability(
    IN p_hotel_id INT,
    IN p_checkin DATE,
    IN p_checkout DATE
)
BEGIN
    SELECT 
        h.hotel_id,
        h.name,
        h.available_rooms,
        (h.available_rooms - COALESCE(booked.rooms_booked, 0)) as rooms_available
    FROM hotels h
    LEFT JOIN (
        SELECT hotel_id, COUNT(*) as rooms_booked
        FROM bookings
        WHERE hotel_id = p_hotel_id
        AND status IN ('confirmed', 'checked_in')
        AND (
            (checkin_date <= p_checkin AND checkout_date > p_checkin) OR
            (checkin_date < p_checkout AND checkout_date >= p_checkout) OR
            (checkin_date >= p_checkin AND checkout_date <= p_checkout)
        )
        GROUP BY hotel_id
    ) booked ON h.hotel_id = booked.hotel_id
    WHERE h.hotel_id = p_hotel_id;
END //
DELIMITER ;

-- ===================================
-- GRANT PERMISSIONS
-- ===================================
-- For development: grant all to root@localhost
GRANT ALL PRIVILEGES ON staybnb_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;

-- ===================================
-- DATABASE COMPLETE
-- ===================================
SELECT 'Database setup complete!' as status;