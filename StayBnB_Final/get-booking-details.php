<?php
/**
 * StayBnB - Get Booking Details API
 * CREATE NEW FILE: get-booking-details.php
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$booking_id = intval($_GET['booking_id'] ?? 0);

if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

$stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.location 
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.hotel_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

$booking = $result->fetch_assoc();
echo json_encode(['success' => true, 'booking' => $booking]);

$conn->close();
?>