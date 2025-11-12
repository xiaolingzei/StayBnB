<?php
require_once __DIR__ . '/../../config/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$booking_id = $_POST['booking_id'] ?? '';
if (!$booking_id) {
    echo json_encode(['message' => 'Booking ID missing.']);
    exit;
}

$sql = "UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
if ($stmt->execute()) {
    echo json_encode(['message' => 'Booking cancelled successfully.']);
} else {
    echo json_encode(['message' => 'Failed to cancel booking.']);
}
$stmt->close();
$conn->close();
?>
