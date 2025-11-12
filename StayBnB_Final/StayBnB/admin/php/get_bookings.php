<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT b.booking_id, b.full_name, b.email, b.checkin, b.checkout, b.status, h.name AS hotel_name
        FROM bookings b LEFT JOIN hotels h ON h.hotel_id = b.hotel_id
        ORDER BY b.created_at DESC";

$res = $conn->query($sql);
$out = [];
if ($res) {
    while ($row = $res->fetch_assoc()) $out[] = $row;
}

echo json_encode(['success'=>true,'data'=>$out]);
$conn->close();
?>