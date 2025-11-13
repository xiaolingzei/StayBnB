<?php
define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = floatval($_POST['rating'] ?? 0);
    $comment = sanitize_input($_POST['comment'] ?? '');
    $booking_id = intval($_POST['booking_id'] ?? 0);
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
        exit;
    }
    
    // Get booking details
    $stmt = $conn->prepare("SELECT hotel_id FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    
    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews (booking_id, user_id, hotel_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'approved')");
    $stmt->bind_param("iiids", $booking_id, $_SESSION['user_id'], $booking['hotel_id'], $rating, $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit rating']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>