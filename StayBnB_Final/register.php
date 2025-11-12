<?php
session_start();
require_once __DIR__ . '/config/db_connect.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD']!=='POST') { echo json_encode(['error'=>'Invalid method.']); exit; }
$fullname = trim($_POST['fullname'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? ''; $confirm = $_POST['confirm'] ?? '';
if ($fullname==''||$email==''||$password==''||$password!=$confirm){ echo json_encode(['error'=>'All fields required and passwords must match']); exit; }
$stmt=$conn->prepare('SELECT user_id FROM users WHERE email=?'); $stmt->bind_param('s',$email); $stmt->execute(); $stmt->store_result();
if($stmt->num_rows>0){ echo json_encode(['error'=>'Email already registered']); exit; } $stmt->close();
$hash = password_hash($password,PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (fullname,email,password_hash,created_at) VALUES (?,?,?,NOW())'); $stmt->bind_param('sss',$fullname,$email,$hash);
if($stmt->execute()){ $_SESSION['user_id']=$conn->insert_id; $_SESSION['fullname']=$fullname; echo json_encode(['success'=>'Registered']); } else { echo json_encode(['error'=>'Registration failed: '.$conn->error]); }
$stmt->close(); $conn->close();
?>