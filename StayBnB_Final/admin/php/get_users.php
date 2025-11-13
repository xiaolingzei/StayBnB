<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

$result = $conn->query("SELECT user_id, fullname, email FROM users ORDER BY user_id DESC");
$users = [];

while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
