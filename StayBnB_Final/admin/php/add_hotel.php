<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';


$name = $_POST['name'];
$location = $_POST['location'];
$description = $_POST['description'];
$price = $_POST['price'];
$image_url = $_POST['image_url'];

if ($name && $location && $price) {
  $stmt = $conn->prepare("INSERT INTO hotels (name, location, description, price, image_url) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssds", $name, $location, $description, $price, $image_url);
  if ($stmt->execute()) {
    echo "<script>alert('Hotel added successfully'); window.location='../hotels.html';</script>";
  } else {
    echo "<script>alert('Error adding hotel'); window.history.back();</script>";
  }
} else {
  echo "<script>alert('All fields required'); window.history.back();</script>";
}
?>
