
DROP DATABASE IF EXISTS staybnb_db;
CREATE DATABASE staybnb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE staybnb_db;

CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  fullname VARCHAR(200) DEFAULT 'Administrator',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hotels (
  hotel_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  location VARCHAR(255) DEFAULT '',
  price DECIMAL(10,2) DEFAULT 0,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  hotel_id INT NOT NULL,
  user_fullname VARCHAR(255) NOT NULL,
  user_email VARCHAR(255) NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  guests INT DEFAULT 1,
  total_price DECIMAL(10,2) DEFAULT 0,
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id) ON DELETE CASCADE
);

-- Insert admin and demo user. Passwords are stored plaintext as fallback if hashing not applied.
INSERT INTO admins (email, password_hash, fullname) VALUES
('theouypro08@gmail.com', 'Xiaolingz3!', 'Project Admin');

INSERT INTO users (fullname, email, password_hash) VALUES
('Demo User', 'demo@staybnb.com', 'demo123');

INSERT INTO hotels (name, location, price, description) VALUES
('Sunset Haven', 'Morong, Bataan', 2500.00, 'A cozy seaside inn with beautiful sunsets.'),
('Ocean Breeze Resort', 'Bagac, Bataan', 4500.00, 'Beachfront resort with full amenities.');

INSERT INTO bookings (user_id, hotel_id, user_fullname, user_email, checkin, checkout, guests, total_price) VALUES
(1, 1, 'Demo User', 'demo@staybnb.com', '2025-11-10', '2025-11-12', 2, 5000.00);

