<?php
// login.php - authenticate users
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: login.html?error=1');
    exit;
}

$pdo = require __DIR__ . '/db.php';

$stmt = $pdo->prepare('SELECT id, fullname, password_hash FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    header('Location: login.html?error=1');
    exit;
}

// Success: set session and redirect
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['fullname'];

header('Location: index.html');
exit;

?>
