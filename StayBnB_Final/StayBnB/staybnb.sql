-- staybnb.sql - schema for StayBnB minimal users table
CREATE DATABASE IF NOT EXISTS staybnb DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staybnb;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(191) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
