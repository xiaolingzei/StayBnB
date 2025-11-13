<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';


if ($id) {
  $stmt = $conn->prepare("DELETE FROM hotels WHERE hotel_id = ?");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    echo "Hotel deleted successfully.";
  } else {
    echo "Error deleting hotel.";
  }
} else {
  echo "Invalid request.";
}
?>
