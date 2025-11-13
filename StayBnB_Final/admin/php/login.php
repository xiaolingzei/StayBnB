<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';



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

if (password_verify($password, $row['password_hash'])) {
    // Success
    $_SESSION['admin_id'] = $row['admin_id'];
    $_SESSION['admin_email'] = $row['email'];
    redirect('../index.php', 'Welcome back!', 'success');
} else {
    redirect('../login.php', 'Invalid credentials', 'error');
}
?>
