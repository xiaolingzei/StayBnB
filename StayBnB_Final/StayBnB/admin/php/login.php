<?php
session_start();
require_once __DIR__ . '/../../../config/db_connect.php';

$username = trim($_POST['username']);
$password = trim($_POST['password']);

if (empty($username) || empty($password)) {
  die("Please fill in both fields.");
}

$sql = "SELECT * FROM admins WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  if (password_verify($password, $row['password_hash'])) {
    $_SESSION['admin_id'] = $row['admin_id'];
    $_SESSION['username'] = $row['username'];
    header("Location: ../index.html");
    exit();
  } else {
    echo "<script>alert('Incorrect password'); window.location='../login.html';</script>";
  }
} else {
  echo "<script>alert('User not found'); window.location='../login.html';</script>";
}
?>
