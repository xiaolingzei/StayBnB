<?php
// Canonical DB connection for StayBnB_Final (error display disabled)
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'staybnb_db';
// ini_set('display_errors',1); error_reporting(E_ALL);

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    error_log("DB connection error: " . $conn->connect_error);
    echo json_encode(['success'=>false,'message'=>'Database connection error.']);
    exit;
}
?>