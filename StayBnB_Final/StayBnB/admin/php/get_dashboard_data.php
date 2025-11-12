<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$totals = ['hotels'=>0,'bookings'=>0,'users'=>0];

$res = $conn->query("SELECT COUNT(*) AS c FROM hotels");
if ($res) $totals['hotels'] = (int)$res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM bookings");
if ($res) $totals['bookings'] = (int)$res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) AS c FROM users");
if ($res) $totals['users'] = (int)$res->fetch_assoc()['c'];

$recent = [];
$res = $conn->query("SELECT b.booking_id AS id, b.full_name, b.email, b.checkin, b.checkout, b.created_at, h.name AS hotel_name
                     FROM bookings b LEFT JOIN hotels h ON h.hotel_id = b.hotel_id
                     ORDER BY b.created_at DESC LIMIT 6");

if ($res) {
    while ($row = $res->fetch_assoc()) $recent[] = $row;
}

echo json_encode(['success'=>true,'totals'=>$totals,'recent'=>$recent]);
$conn->close();
?>