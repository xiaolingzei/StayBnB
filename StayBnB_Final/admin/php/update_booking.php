<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

$id = $_POST['booking_id'] ?? null;
$status = $_POST['status'] ?? '';

if ($id && $status) {
  $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE booking_id=?");
  $stmt->bind_param("si", $status, $id);
  if ($stmt->execute()) {
    echo "Booking status updated to $status.";
  } else {
    echo "Error updating booking.";
  }
} else {
  echo "Invalid request.";
}
?>
