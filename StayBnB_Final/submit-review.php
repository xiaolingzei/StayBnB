<?php
/**
 * StayBnB - Submit Hotel Review
 * CREATE NEW FILE: submit-review.php
 * Users can submit reviews with star ratings after their stay
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $rating = floatval($_POST['rating'] ?? 0);
    $title = sanitize_input($_POST['title'] ?? '');
    $comment = sanitize_input($_POST['comment'] ?? '');
    
    // Validation
    if ($hotel_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid hotel']);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5 stars']);
        exit;
    }
    
    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Please write a review']);
        exit;
    }
    
    // Check if user has a completed booking at this hotel
    $stmt = $conn->prepare("
        SELECT booking_id FROM bookings 
        WHERE user_id = ? 
        AND hotel_id = ? 
        AND status IN ('checked_out', 'confirmed')
        LIMIT 1
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You can only review hotels you have booked']);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    if ($booking_id === 0) {
        $booking_id = $booking['booking_id'];
    }
    
    // Check if user already reviewed this hotel
    $stmt = $conn->prepare("
        SELECT review_id FROM reviews 
        WHERE user_id = ? AND hotel_id = ?
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this hotel']);
        exit;
    }
    
    // Insert review
    $stmt = $conn->prepare("
        INSERT INTO reviews (booking_id, user_id, hotel_id, rating, title, comment, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'approved', NOW())
    ");
    $stmt->bind_param("iiidss", $booking_id, $_SESSION['user_id'], $hotel_id, $rating, $title, $comment);
    
    if ($stmt->execute()) {
        // Update hotel's average rating
        $conn->query("
            UPDATE hotels h
            SET h.star_rating = (
                SELECT AVG(r.rating)
                FROM reviews r
                WHERE r.hotel_id = h.hotel_id AND r.status = 'approved'
            )
            WHERE h.hotel_id = $hotel_id
        ");
        
        log_activity($conn, 'review_submitted', 'reviews', $conn->insert_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you for your review! Your feedback helps other travelers.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>