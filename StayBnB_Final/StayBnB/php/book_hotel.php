<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// Collect POST data safely
$hotel_id   = $_POST['hotel_id']   ?? '';
$full_name  = $_POST['full_name']  ?? '';
$email      = $_POST['email']      ?? '';
$checkin    = $_POST['checkin']    ?? '';
$checkout   = $_POST['checkout']   ?? '';
$guests     = $_POST['guests']     ?? '';
$total_price = $_POST['total_price'] ?? '';

// Check required fields
if (empty($hotel_id) || empty($full_name) || empty($email) || empty($checkin) || empty($checkout) || empty($guests)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Insert booking
$stmt = $conn->prepare("INSERT INTO bookings (hotel_id, full_name, email, checkin, checkout, guests, total_price)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("isssssd", $hotel_id, $full_name, $email, $checkin, $checkout, $guests, $total_price);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Booking successful!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: '.$conn->error]);
}

$stmt->close();
$conn->close();
?>
