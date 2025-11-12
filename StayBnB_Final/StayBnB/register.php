<?php
// register.php - handles user registration
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

$errors = [];
if ($fullname === '') { $errors[] = 'Full name is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
if (strlen($password) < 6) { $errors[] = 'Password should be at least 6 characters.'; }
if ($password !== $confirm) { $errors[] = 'Passwords do not match.'; }

if ($errors) {
    // send first error back via query param (simple UX)
    $msg = urlencode($errors[0]);
    header("Location: register.html?error=$msg");
    exit;
}

$pdo = require __DIR__ . '/db.php';

// check if email already exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header('Location: register.html?error=' . urlencode('Email already registered'));
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO users (fullname, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
try {
    $stmt->execute([$fullname, $email, $hash]);
} catch (Exception $e) {
    header('Location: register.html?error=' . urlencode('Unable to create account'));
    exit;
}

// Redirect to login with success flag
header('Location: login.html?registered=1');
exit;

?>
