<?php
// Database connection for StayBnB Admin Panel
$host = "localhost";
$user = "root";
$pass = "";
$db   = "staybnb_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
