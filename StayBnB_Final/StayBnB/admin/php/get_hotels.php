<?php
require_once __DIR__ . '/../../../config/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT hotel_id, name, description, price, image_url FROM hotels";
$result = $conn->query($sql);

$hotels = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
}

echo json_encode($hotels);
$conn->close();
?>
