<?php
require_once __DIR__ . '/../../config/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$hotel_id = $_POST['hotel_id'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$checkin = $_POST['checkin'] ?? '';
$checkout = $_POST['checkout'] ?? '';
$guests = $_POST['guests'] ?? 1;

if (!$hotel_id || !$fullname || !$email || !$checkin || !$checkout) {
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$sql_price = "SELECT price FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql_price);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
if ($hotel = $result->fetch_assoc()) {
    $price = floatval($hotel['price']);
} else {
    echo json_encode(['error' => 'Invalid hotel ID.']);
    exit;
}
$stmt->close();

$days = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);
if ($days <= 0) {
    echo json_encode(['error' => 'Check-out must be after check-in.']);
    exit;
}

$total_price = $days * $price;

$sql = "INSERT INTO bookings (hotel_id, user_fullname, user_email, checkin, checkout, guests, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssid", $hotel_id, $fullname, $email, $checkin, $checkout, $guests, $total_price);
if ($stmt->execute()) {
    echo json_encode(['success' => 'Booking confirmed! Thank you for choosing StayBnB.']);
} else {
    echo json_encode(['error' => 'Failed to save booking: '.$conn->error]);
}
$stmt->close();
$conn->close();
?>
