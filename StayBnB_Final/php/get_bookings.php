<?php
require_once __DIR__ . '/../../config/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$email = $_GET['email'] ?? '';
if (empty($email)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT b.*, h.name AS hotel_name 
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.hotel_id
        WHERE b.user_email = ?
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode($bookings);
$stmt->close();
$conn->close();
?>
