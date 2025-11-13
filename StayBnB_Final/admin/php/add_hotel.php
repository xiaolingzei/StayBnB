<?php
/**
 * StayBnB - Add Hotel Handler
 * Location: admin/php/add_hotel.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $location = sanitize_input($_POST['location']);
    $address = sanitize_input($_POST['address']);
    $description = sanitize_input($_POST['description'] ?? '');
    $price_per_night = floatval($_POST['price_per_night']);
    $star_rating = floatval($_POST['star_rating'] ?? 3.0);
    $available_rooms = intval($_POST['available_rooms'] ?? 10);
    $total_rooms = intval($_POST['total_rooms'] ?? $available_rooms);
    $phone = sanitize_input($_POST['phone'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $amenities = sanitize_input($_POST['amenities'] ?? 'Free WiFi,Parking');
    $image_url = sanitize_input($_POST['image_url'] ?? '');

    // Validation
    if (empty($name) || empty($location) || empty($address) || $price_per_night <= 0) {
        redirect('../hotels.php', 'Please fill in all required fields', 'error');
    }

    // Insert hotel
    $stmt = $conn->prepare("
        INSERT INTO hotels (
            name, location, address, description, phone, email,
            star_rating, price_per_night, available_rooms, total_rooms,
            amenities, status, featured
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 0)
    ");
    
    $stmt->bind_param(
        "ssssssddiis", 
        $name, $location, $address, $description, $phone, $email,
        $star_rating, $price_per_night, $available_rooms, $total_rooms,
        $amenities
    );
    
    if ($stmt->execute()) {
        $hotel_id = $conn->insert_id;
        
        // Add primary image if provided
        if (!empty($image_url)) {
            $stmt = $conn->prepare("
                INSERT INTO hotel_images (hotel_id, image_url, is_primary, display_order) 
                VALUES (?, ?, 1, 0)
            ");
            $stmt->bind_param("is", $hotel_id, $image_url);
            $stmt->execute();
        }
        
        // Log activity
        log_activity($conn, 'hotel_created', 'hotels', $hotel_id);
        
        redirect('../hotels.php', 'Hotel added successfully!', 'success');
    } else {
        error_log("Add hotel error: " . $conn->error);
        redirect('../hotels.php', 'Error adding hotel. Please try again.', 'error');
    }
} else {
    redirect('../hotels.php', 'Invalid request method', 'error');
}
?>