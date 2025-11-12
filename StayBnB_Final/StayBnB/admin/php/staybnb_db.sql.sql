CREATE DATABASE IF NOT EXISTS staybnb_db;
USE staybnb_db;

CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, password_hash)
VALUES ('root', '$2y$10$KZ0bQt3VxQpY2yZFdY0KgeUlLFS0N1lFY6s4YijcNoC0VwxybROqG'); 

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100),
  email VARCHAR(100),
  password_hash VARCHAR(255)
);

CREATE TABLE hotels (
  hotel_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  location VARCHAR(100),
  description TEXT,
  price DECIMAL(10,2),
  image_url VARCHAR(255)
);

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  hotel_id INT,
  checkin DATE,
  checkout DATE,
  status VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (hotel_id) REFERENCES hotels(hotel_id)
);
