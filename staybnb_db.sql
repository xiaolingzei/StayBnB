-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 01:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `staybnb_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `check_hotel_availability` (IN `p_hotel_id` INT, IN `p_checkin` DATE, IN `p_checkout` DATE)   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `admin_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, NULL, 'user_registered', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 04:40:32'),
(2, 2, NULL, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 07:33:39'),
(3, 3, NULL, 'user_registered', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 07:34:59'),
(4, 2, NULL, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 08:41:58'),
(5, 3, NULL, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 08:43:17'),
(6, 3, NULL, 'booking_created', 'bookings', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 08:49:59'),
(7, 3, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 08:59:33'),
(8, NULL, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 09:07:27'),
(9, 3, 1, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 10:41:40'),
(10, 2, NULL, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 10:41:48'),
(11, 3, NULL, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 10:44:25'),
(12, 3, NULL, 'booking_created', 'bookings', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 10:45:03'),
(13, 3, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 10:56:26'),
(14, 2, NULL, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 12:06:33'),
(15, NULL, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 12:14:09'),
(16, NULL, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:14:53'),
(17, 3, 1, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:32:12'),
(18, 3, 1, 'booking_cancelled', 'bookings', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:32:22'),
(19, 3, 1, 'booking_created', 'bookings', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:32:59'),
(20, 3, 1, 'report_submitted', 'user_reports', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:34:19'),
(21, NULL, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 13:34:40'),
(22, 3, NULL, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:25:09'),
(23, 3, NULL, 'booking_created', 'bookings', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:25:40'),
(24, 3, NULL, 'booking_created', 'bookings', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:36:13'),
(25, 3, NULL, 'payment_completed', 'payments', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:36:43'),
(26, 3, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:37:02'),
(27, 3, 1, 'booking_cancelled', 'bookings', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:39:30'),
(28, 3, 1, 'booking_cancelled', 'bookings', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 17:39:43'),
(29, NULL, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 21:40:27'),
(30, 2, 1, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 21:59:44'),
(31, 3, NULL, 'user_login', 'users', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:07:30'),
(32, 3, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:08:32'),
(33, 2, NULL, 'user_login', 'users', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:12:45'),
(34, 2, NULL, 'booking_created', 'bookings', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:13:17'),
(35, 2, NULL, 'payment_completed', 'payments', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:13:33'),
(36, 2, 1, 'admin_login', 'admins', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 22:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','staff') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password_hash`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@staybnb.com', '$2y$12$YooUKD26pFWi0QnC66QVM.YINCU79U/MHvf7t1l.YqNSweF8.YjeG', 'super_admin', 'active', '2025-11-13 22:14:13', '2025-11-13 04:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_ref` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `guest_fullname` varchar(100) NOT NULL,
  `guest_email` varchar(100) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `num_guests` int(11) DEFAULT 1,
  `num_nights` int(11) NOT NULL,
  `room_rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','checked_in','checked_out','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `booking_ref`, `user_id`, `hotel_id`, `guest_fullname`, `guest_email`, `guest_phone`, `checkin_date`, `checkout_date`, `num_guests`, `num_nights`, `room_rate`, `total_amount`, `status`, `payment_status`, `payment_method`, `special_requests`, `cancelled_at`, `created_at`, `updated_at`) VALUES
(1, 'BK202511137CB94A', 3, 8, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '2025-11-13', '2025-11-15', 3, 2, 1500.00, 3000.00, 'cancelled', 'unpaid', NULL, '', NULL, '2025-11-13 08:49:59', '2025-11-13 09:00:30'),
(2, 'BK20251113F11281', 3, 7, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '2025-11-17', '2025-11-22', 2, 5, 6000.00, 30000.00, 'cancelled', 'unpaid', NULL, '', '2025-11-13 13:32:22', '2025-11-13 10:45:03', '2025-11-13 13:32:22'),
(3, 'BK20251113B4EBE4', 3, 1, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '2025-11-19', '2025-11-26', 2, 7, 3500.00, 24500.00, 'cancelled', 'unpaid', NULL, '', '2025-11-13 17:39:43', '2025-11-13 13:32:59', '2025-11-13 17:39:43'),
(4, 'BK202511144A7BE2', 3, 5, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '2025-11-14', '2025-11-18', 2, 4, 2200.00, 8800.00, 'cancelled', 'unpaid', NULL, '', NULL, '2025-11-13 17:25:40', '2025-11-13 17:41:23'),
(5, 'BK20251114DE84B2', 3, 2, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '2025-11-26', '2025-11-28', 2, 2, 2500.00, 5000.00, 'cancelled', 'paid', 'maya', '', '2025-11-13 17:39:30', '2025-11-13 17:36:13', '2025-11-13 17:39:30'),
(6, 'BK20251114D91326', 2, 13, 'Theo Andres Uy', 'theouypro08@gmail.com', '09608459577', '2025-12-08', '2025-12-11', 3, 3, 3800.00, 11400.00, 'confirmed', 'paid', 'gcash', '', NULL, '2025-11-13 22:13:17', '2025-11-13 22:13:33');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `hotel_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `star_rating` decimal(2,1) DEFAULT 3.0,
  `price_per_night` decimal(10,2) NOT NULL,
  `available_rooms` int(11) DEFAULT 10,
  `total_rooms` int(11) DEFAULT 10,
  `check_in_time` time DEFAULT '14:00:00',
  `check_out_time` time DEFAULT '12:00:00',
  `amenities` text DEFAULT NULL,
  `policies` text DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`hotel_id`, `name`, `location`, `address`, `description`, `phone`, `email`, `star_rating`, `price_per_night`, `available_rooms`, `total_rooms`, `check_in_time`, `check_out_time`, `amenities`, `policies`, `featured`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bataan White Corals Beach Resort', 'Morong', 'Sitio Alas-asin, Brgy. Nagbalayong, Morong, Bataan', 'A beautiful beach resort with white sand beaches and crystal clear waters. Perfect for family getaways and romantic escapes.', '0915-234-5678', 'info@whitecoralsbataan.com', 4.5, 3500.00, 15, 20, '14:00:00', '12:00:00', 'Free WiFi,Swimming Pool,Restaurant,Parking,Beach Access', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(2, 'The Plaza Hotel Balanga', 'Balanga City', '123 Capitol Drive, Balanga City, Bataan', 'Modern hotel in the heart of Balanga City. Close to historical sites and government offices.', '0917-345-6789', 'reservations@plazahotelbalanga.com', 4.0, 2500.00, 25, 30, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Conference Room,Parking,24/7 Reception', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(3, 'Teresita\'s Hotel & Resort', 'Bagac', 'National Road, Bagac, Bataan', 'Comfortable accommodation with great amenities. Near Mt. Samat and other historical landmarks.', '0919-456-7890', 'teresitas@gmail.com', 3.5, 2000.00, 20, 25, '14:00:00', '12:00:00', 'Free WiFi,Swimming Pool,Restaurant,Parking', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(4, 'The Miele Hotel', 'Balanga City', '456 Main Street, Balanga City, Bataan', 'Boutique hotel offering personalized service and comfortable rooms.', '0918-567-8901', 'info@mielehotel.ph', 4.2, 2800.00, 12, 15, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Gym,Parking', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(5, 'Bataan Beach Resort', 'Morong', 'Brgy. Mabayo, Morong, Bataan', 'Affordable beach resort perfect for groups and families.', '0916-678-9012', 'bataan.beach@yahoo.com', 3.8, 2200.00, 18, 22, '14:00:00', '12:00:00', 'Free WiFi,Beach Access,Restaurant,Parking,Kayaking', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(6, 'Peninsula Hotel Bataan', 'Balanga City', '789 Peninsula Avenue, Balanga City, Bataan', 'Premium hotel with excellent facilities and service.', '0917-789-0123', 'reservations@peninsulahotelbataan.com', 4.8, 4500.00, 10, 12, '14:00:00', '12:00:00', 'Free WiFi,Swimming Pool,Spa,Restaurant,Gym,Conference Room', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(7, 'Las Casas Filipinas de Acuzar', 'Bagac', 'Brgy. Ibaba, Bagac, Bataan', 'Heritage resort featuring restored Spanish-Filipino colonial houses.', '0918-890-1234', 'info@lascasasfilipinas.com', 5.0, 6000.00, 8, 10, '14:00:00', '12:00:00', 'Free WiFi,Swimming Pool,Restaurant,Museum,Heritage Tours', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(8, 'Sunset View Inn', 'Mariveles', 'Waterfront Road, Mariveles, Bataan', 'Budget-friendly inn with beautiful sunset views over Manila Bay.', '0915-901-2345', 'sunsetviewinn@gmail.com', 3.0, 1500.00, 15, 18, '14:00:00', '12:00:00', 'Free WiFi,Parking,Fan Rooms,Sea View', NULL, 0, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(9, 'Bataan Peninsula Resort', 'Morong', 'Brgy. Sabang, Morong, Bataan', 'Peaceful beachfront resort perfect for families. Features spacious rooms, water sports activities, and authentic Filipino cuisine. Close to historical war memorial sites.', '0917-555-1234', 'info@bataanpeninsula.com', 4.3, 3200.00, 18, 25, '14:00:00', '12:00:00', 'Free WiFi,Beach Access,Swimming Pool,Restaurant,Kayaking,Parking,Kids Play Area', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(10, 'Mount Samat View Hotel', 'Pilar', 'National Road, Pilar, Bataan', 'Strategically located near Mt. Samat National Shrine. Modern amenities with stunning mountain views. Ideal for history enthusiasts and nature lovers.', '0918-555-2345', 'reservations@mtsamatview.ph', 3.8, 1800.00, 22, 28, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Parking,Tour Desk,Mountain View,Conference Room', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(11, 'Balanga Bay Hotel', 'Balanga City', '234 Bayfront Avenue, Balanga City, Bataan', 'Contemporary hotel overlooking Balanga Bay. Perfect for business travelers and tourists. Walking distance to city attractions and government offices.', '0919-555-3456', 'info@balangabay.com', 4.4, 3000.00, 15, 20, '14:00:00', '12:00:00', 'Free WiFi,Swimming Pool,Restaurant,Gym,Business Center,Parking,Bay View', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(12, 'Limay Beach Resort', 'Limay', 'Coastal Road, Limay, Bataan', 'Affordable beach resort with family-friendly atmosphere. Offers water sports, beach volleyball, and sunset viewing spots. Great value for budget travelers.', '0915-555-4567', 'limaybeach@yahoo.com', 3.5, 1600.00, 25, 30, '14:00:00', '12:00:00', 'Free WiFi,Beach Access,Restaurant,Parking,Water Sports,BBQ Area', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(13, 'Heritage Inn Bataan', 'Balanga City', '567 Heritage Street, Balanga City, Bataan', 'Boutique hotel celebrating Bataan\'s rich history. Elegant rooms with modern comfort. Near museums and cultural sites.', '0916-555-5678', 'stay@heritageinnbataan.com', 5.0, 3800.00, 10, 15, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Spa,Heritage Tours,Parking,Library,Art Gallery', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 22:14:04'),
(14, 'Mariveles Port Hotel', 'Mariveles', '789 Port Area, Mariveles, Bataan', 'Convenient hotel near Mariveles Port. Ideal for travelers heading to Corregidor Island. Clean, comfortable, and budget-friendly.', '0917-555-6789', 'info@marivelesport.com', 3.2, 1400.00, 20, 24, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Parking,Port Shuttle,Air Conditioning', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(15, 'Bagac Bay Beach Resort', 'Bagac', 'Brgy. Quinawan, Bagac, Bataan', 'Secluded beach resort with pristine waters. Perfect for romantic getaways and peaceful retreats. Offers diving and snorkeling activities.', '0918-555-7890', 'reservations@bagacbay.ph', 4.7, 4200.00, 12, 16, '14:00:00', '12:00:00', 'Free WiFi,Private Beach,Swimming Pool,Restaurant,Diving Center,Spa,Parking', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(16, 'City Center Hotel Balanga', 'Balanga City', '890 Downtown Balanga, Bataan', 'Modern budget hotel in the city center. Walking distance to shopping malls, restaurants, and tourist attractions. Perfect for short stays.', '0919-555-8901', 'citycenterbataan@gmail.com', 3.6, 1500.00, 28, 35, '14:00:00', '12:00:00', 'Free WiFi,Restaurant,Parking,24/7 Reception,Air Conditioning', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(17, 'Hermosa Beach Villa', 'Hermosa', 'Brgy. Mabuco, Hermosa, Bataan', 'Charming beach villa with cozy rooms and friendly staff. Family-run establishment offering personalized service. Great seafood restaurant on-site.', '0915-555-9012', 'hermosabeachvilla@gmail.com', 4.0, 2400.00, 14, 18, '14:00:00', '12:00:00', 'Free WiFi,Beach Access,Restaurant,Parking,Seafood Specialties,Garden', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50'),
(18, 'Dinalupihan Traveler\'s Inn', 'Dinalupihan', '123 Highway Junction, Dinalupihan, Bataan', 'Convenient stopover for travelers. Clean rooms, friendly service, and affordable rates. Near SCTEX entrance and local restaurants.', '0916-555-0123', 'dinalupihan.inn@yahoo.com', 3.4, 1200.00, 20, 25, '14:00:00', '12:00:00', 'Free WiFi,Parking,Restaurant,24/7 Reception,Air Conditioning', NULL, 0, 'active', '2025-11-13 21:56:50', '2025-11-13 21:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_images`
--

CREATE TABLE `hotel_images` (
  `image_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `caption` varchar(200) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_images`
--

INSERT INTO `hotel_images` (`image_id`, `hotel_id`, `image_url`, `is_primary`, `caption`, `display_order`, `created_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4', 1, NULL, 0, '2025-11-13 04:17:51'),
(2, 2, 'https://images.unsplash.com/photo-1566073771259-6a8506099945', 1, NULL, 0, '2025-11-13 04:17:51'),
(3, 3, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa', 1, NULL, 0, '2025-11-13 04:17:51'),
(4, 4, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb', 1, NULL, 0, '2025-11-13 04:17:51'),
(5, 5, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d', 1, NULL, 0, '2025-11-13 04:17:51'),
(6, 6, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b', 1, NULL, 0, '2025-11-13 04:17:51'),
(7, 7, 'https://images.unsplash.com/photo-1564501049412-61c2a3083791', 1, NULL, 0, '2025-11-13 04:17:51'),
(8, 8, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304', 1, NULL, 0, '2025-11-13 04:17:51'),
(9, 9, 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7', 1, NULL, 0, '2025-11-13 21:56:50'),
(10, 10, 'https://images.unsplash.com/photo-1566073771259-6a8506099945', 1, NULL, 0, '2025-11-13 21:56:50'),
(11, 11, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa', 1, NULL, 0, '2025-11-13 21:56:50'),
(12, 12, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d', 1, NULL, 0, '2025-11-13 21:56:50'),
(13, 13, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb', 1, NULL, 0, '2025-11-13 21:56:50'),
(14, 14, 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4', 1, NULL, 0, '2025-11-13 21:56:50'),
(15, 15, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b', 1, NULL, 0, '2025-11-13 21:56:50'),
(16, 16, 'https://images.unsplash.com/photo-1566073771259-6a8506099945', 1, NULL, 0, '2025-11-13 21:56:50'),
(17, 17, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d', 1, NULL, 0, '2025-11-13 21:56:50'),
(18, 18, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304', 1, NULL, 0, '2025-11-13 21:56:50');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_ref` varchar(20) NOT NULL,
  `payment_method` enum('gcash','maya','card','cash') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `masked_details` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `payment_ref`, `payment_method`, `amount`, `masked_details`, `status`, `created_at`) VALUES
(1, 5, 'PAY2025111472BA07F1', 'maya', 5000.00, '0960****577', 'completed', '2025-11-13 17:36:43'),
(2, 6, 'PAY2025111480D9CE4D', 'gcash', 11400.00, '0960****577', 'completed', '2025-11-13 22:13:33');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL CHECK (`rating` >= 1.0 and `rating` <= 5.0),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `helpful_count` int(11) DEFAULT 0,
  `response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `booking_id`, `user_id`, `hotel_id`, `rating`, `title`, `comment`, `status`, `created_at`, `helpful_count`, `response`, `responded_at`) VALUES
(1, 6, 2, 13, 5.0, 'StayBnB Service Rating', '', 'approved', '2025-11-13 22:14:04', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spots`
--

CREATE TABLE `tourist_spots` (
  `spot_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tourist_spots`
--

INSERT INTO `tourist_spots` (`spot_id`, `name`, `location`, `description`, `category`, `latitude`, `longitude`, `image_url`, `created_at`) VALUES
(1, 'Mt. Samat National Shrine', 'Pilar', 'Historic WWII memorial with a towering cross', 'Historical', 14.6333000, 120.5167000, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d', '2025-11-13 09:05:48'),
(2, 'Las Casas Filipinas de Acuzar', 'Bagac', 'Heritage resort featuring Spanish colonial houses', 'Historical', 14.6000000, 120.4167000, 'https://images.unsplash.com/photo-1564501049412-61c2a3083791', '2025-11-13 09:05:48'),
(3, 'Dunsulan Falls', 'Bagac', 'Beautiful waterfall in the mountains', 'Mountain', 14.5833000, 120.4333000, 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e', '2025-11-13 09:05:48'),
(4, 'Pawikan Conservation Center', 'Morong', 'Sea turtle conservation and hatching site', 'Beach', 14.6833000, 120.2833000, 'https://images.unsplash.com/photo-1559827260-dc66d52bef19', '2025-11-13 09:05:48'),
(5, 'Balanga City Plaza', 'Balanga City', 'Historic town plaza and park', 'Park', 14.6769000, 120.5364000, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa', '2025-11-13 09:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('active','suspended','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `email`, `phone`, `password_hash`, `address`, `verification_token`, `is_verified`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Juan Dela Cruz', 'test@example.com', '09171234567', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIq8G9xqKW', NULL, NULL, 1, 'active', '2025-11-13 04:17:51', '2025-11-13 04:17:51'),
(2, 'Theo Andres Uy', 'theouypro08@gmail.com', '09608459577', '$2y$12$iYWWnYEIa9qOrx3VSK5agOcPqWMkARtaDlB/L3ioVCCfjh7gIWldO', NULL, '89a55af618c5a8c17eb8486172029ae37d7f691fb7e713dc508ae8a02cb27f48', 0, 'active', '2025-11-13 04:40:32', '2025-11-13 14:18:55'),
(3, 'Oeht Yu', 'oehtyu04082004@gmail.com', '09608459577', '$2y$12$uoIUIxCKniQPMuFco8enFeM5rMciwjx3gnPCXKRQk4NzOBzQiA/QO', '', '32d965206093d1b9e5decb065ed830c5', 0, 'active', '2025-11-13 07:34:59', '2025-11-13 08:49:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `preference_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preferred_location` varchar(100) DEFAULT NULL,
  `preferred_price_min` decimal(10,2) DEFAULT NULL,
  `preferred_price_max` decimal(10,2) DEFAULT NULL,
  `preferred_amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reports`
--

CREATE TABLE `user_reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_reports`
--

INSERT INTO `user_reports` (`report_id`, `user_id`, `subject`, `category`, `description`, `status`, `admin_response`, `created_at`, `updated_at`) VALUES
(1, 3, 'FUCK YOUUU', 'Hotel Issue', 'BRUHH', 'closed', '', '2025-11-13 13:34:19', '2025-11-13 22:26:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_booking_details`
-- (See below for the actual view)
--
CREATE TABLE `v_booking_details` (
`booking_id` int(11)
,`booking_ref` varchar(20)
,`user_id` int(11)
,`hotel_id` int(11)
,`guest_fullname` varchar(100)
,`guest_email` varchar(100)
,`guest_phone` varchar(20)
,`checkin_date` date
,`checkout_date` date
,`num_guests` int(11)
,`num_nights` int(11)
,`room_rate` decimal(10,2)
,`total_amount` decimal(10,2)
,`status` enum('pending','confirmed','checked_in','checked_out','cancelled')
,`payment_status` enum('unpaid','paid','refunded')
,`payment_method` varchar(50)
,`special_requests` text
,`cancelled_at` timestamp
,`created_at` timestamp
,`updated_at` timestamp
,`hotel_name` varchar(200)
,`location` varchar(100)
,`address` text
,`image_url` varchar(500)
,`user_fullname` varchar(100)
,`user_email` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_hotels_list`
-- (See below for the actual view)
--
CREATE TABLE `v_hotels_list` (
`hotel_id` int(11)
,`name` varchar(200)
,`location` varchar(100)
,`address` text
,`description` text
,`phone` varchar(20)
,`email` varchar(100)
,`star_rating` decimal(2,1)
,`price_per_night` decimal(10,2)
,`available_rooms` int(11)
,`total_rooms` int(11)
,`check_in_time` time
,`check_out_time` time
,`amenities` text
,`policies` text
,`featured` tinyint(1)
,`status` enum('active','inactive','maintenance')
,`created_at` timestamp
,`updated_at` timestamp
,`image_url` varchar(500)
,`avg_rating` decimal(6,5)
,`review_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Structure for view `v_booking_details`
--
DROP TABLE IF EXISTS `v_booking_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_booking_details`  AS SELECT `b`.`booking_id` AS `booking_id`, `b`.`booking_ref` AS `booking_ref`, `b`.`user_id` AS `user_id`, `b`.`hotel_id` AS `hotel_id`, `b`.`guest_fullname` AS `guest_fullname`, `b`.`guest_email` AS `guest_email`, `b`.`guest_phone` AS `guest_phone`, `b`.`checkin_date` AS `checkin_date`, `b`.`checkout_date` AS `checkout_date`, `b`.`num_guests` AS `num_guests`, `b`.`num_nights` AS `num_nights`, `b`.`room_rate` AS `room_rate`, `b`.`total_amount` AS `total_amount`, `b`.`status` AS `status`, `b`.`payment_status` AS `payment_status`, `b`.`payment_method` AS `payment_method`, `b`.`special_requests` AS `special_requests`, `b`.`cancelled_at` AS `cancelled_at`, `b`.`created_at` AS `created_at`, `b`.`updated_at` AS `updated_at`, `h`.`name` AS `hotel_name`, `h`.`location` AS `location`, `h`.`address` AS `address`, `hi`.`image_url` AS `image_url`, `u`.`fullname` AS `user_fullname`, `u`.`email` AS `user_email` FROM (((`bookings` `b` join `hotels` `h` on(`b`.`hotel_id` = `h`.`hotel_id`)) join `users` `u` on(`b`.`user_id` = `u`.`user_id`)) left join `hotel_images` `hi` on(`h`.`hotel_id` = `hi`.`hotel_id` and `hi`.`is_primary` = 1)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_hotels_list`
--
DROP TABLE IF EXISTS `v_hotels_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_hotels_list`  AS SELECT `h`.`hotel_id` AS `hotel_id`, `h`.`name` AS `name`, `h`.`location` AS `location`, `h`.`address` AS `address`, `h`.`description` AS `description`, `h`.`phone` AS `phone`, `h`.`email` AS `email`, `h`.`star_rating` AS `star_rating`, `h`.`price_per_night` AS `price_per_night`, `h`.`available_rooms` AS `available_rooms`, `h`.`total_rooms` AS `total_rooms`, `h`.`check_in_time` AS `check_in_time`, `h`.`check_out_time` AS `check_out_time`, `h`.`amenities` AS `amenities`, `h`.`policies` AS `policies`, `h`.`featured` AS `featured`, `h`.`status` AS `status`, `h`.`created_at` AS `created_at`, `h`.`updated_at` AS `updated_at`, `hi`.`image_url` AS `image_url`, coalesce(avg(`r`.`rating`),`h`.`star_rating`) AS `avg_rating`, count(distinct `r`.`review_id`) AS `review_count` FROM ((`hotels` `h` left join `hotel_images` `hi` on(`h`.`hotel_id` = `hi`.`hotel_id` and `hi`.`is_primary` = 1)) left join `reviews` `r` on(`h`.`hotel_id` = `r`.`hotel_id` and `r`.`status` = 'approved')) WHERE `h`.`status` = 'active' GROUP BY `h`.`hotel_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_ref` (`booking_ref`),
  ADD KEY `idx_booking_ref` (`booking_ref`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_hotel_id` (`hotel_id`),
  ADD KEY `idx_dates` (`checkin_date`,`checkout_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`hotel_id`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_price` (`price_per_night`);

--
-- Indexes for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_hotel_primary` (`hotel_id`,`is_primary`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `payment_ref` (`payment_ref`),
  ADD KEY `idx_payment_ref` (`payment_ref`),
  ADD KEY `idx_booking_id` (`booking_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_hotel_status` (`hotel_id`,`status`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD PRIMARY KEY (`spot_id`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Indexes for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `hotel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `hotel_images`
--
ALTER TABLE `hotel_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  MODIFY `spot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_reports`
--
ALTER TABLE `user_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD CONSTRAINT `hotel_images_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`hotel_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_reports`
--
ALTER TABLE `user_reports`
  ADD CONSTRAINT `user_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
