<?php
session_start();
require_once __DIR__ . '/config/db_connect.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD']!=='POST'){ echo json_encode(['error'=>'Invalid method.']); exit; }
$email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
if($email==''||$password==''){ echo json_encode(['error'=>'All fields required']); exit; }
$stmt = $conn->prepare('SELECT user_id, fullname, password_hash FROM users WHERE email = ?'); $stmt->bind_param('s', $email); $stmt->execute(); $res = $stmt->get_result();
if($row = $res->fetch_assoc()){
    if(password_verify($password, $row['password_hash'])){ $_SESSION['user_id']=$row['user_id']; $_SESSION['fullname']=$row['fullname']; echo json_encode(['success'=>'Logged in']); }
    else if ($password === $row['password_hash']) { $_SESSION['user_id']=$row['user_id']; $_SESSION['fullname']=$row['fullname']; echo json_encode(['success'=>'Logged in']); }
    else { echo json_encode(['error'=>'Invalid credentials']); }
} else { echo json_encode(['error'=>'Invalid credentials']); }
$stmt->close(); $conn->close();
?>