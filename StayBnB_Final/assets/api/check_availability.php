<?php
define('STAYBNB_ACCESS', true);
require_once '../config/db_connect.php';

header('Content-Type: application/json');

$hotel_id = intval($_GET['hotel_id'] ?? 0);
$checkin = sanitize_input($_GET['checkin'] ?? '');
$checkout = sanitize_input($_GET['checkout'] ?? '');

if ($hotel_id <= 0 || empty($checkin) || empty($checkout)) {
    echo json_encode(['available' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Check if hotel has available rooms
$stmt = $conn->prepare("SELECT available_rooms FROM hotels WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['available' => false, 'error' => 'Hotel not found']);
    exit;
}

$hotel = $result->fetch_assoc();

// Check for conflicting bookings
$stmt = $conn->prepare("SELECT COUNT(*) as conflict_count 
                        FROM bookings 
                        WHERE hotel_id = ? 
                        AND status IN ('confirmed', 'checked_in')
                        AND (
                            (checkin_date <= ? AND checkout_date > ?) OR
                            (checkin_date < ? AND checkout_date >= ?) OR
                            (checkin_date >= ? AND checkout_date <= ?)
                        )");
$stmt->bind_param("issssss", $hotel_id, $checkin, $checkin, $checkout, $checkout, $checkin, $checkout);
$stmt->execute();
$conflicts = $stmt->get_result()->fetch_assoc();

$available_rooms = $hotel['available_rooms'] - $conflicts['conflict_count'];

echo json_encode([
    'available' => $available_rooms > 0,
    'available_rooms' => max(0, $available_rooms),
    'dates' => ['checkin' => $checkin, 'checkout' => $checkout]
]);

$conn->close();
?>