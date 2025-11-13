<?php
require_once __DIR__ . '/../../config/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing hotel ID']);
    exit;
}

$hotel_id = intval($_GET['id']);
$sql = "SELECT hotel_id, name, description, price, image_url FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($hotel = $result->fetch_assoc()) {
    echo json_encode($hotel);
} else {
    echo json_encode(['error' => 'Hotel not found']);
}

$stmt->close();
$conn->close();
?>
