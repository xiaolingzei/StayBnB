<?php
/**
 * FIXED rate-service.php - No more "Booking not found" error
 * Replace your existing rate-service.php with this
 */

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
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
        exit;
    }
    
    // FIXED: This is a general service rating (not hotel-specific)
    if ($booking_id === 0 && $hotel_id === 0) {
        // General StayBnB service rating
        // Store in a separate table or just acknowledge
        
        // For now, we'll store it as a review for the most recent booking
        $stmt = $conn->prepare("
            SELECT b.booking_id, b.hotel_id 
            FROM bookings b
            WHERE b.user_id = ? 
            AND b.status IN ('confirmed', 'checked_out')
            ORDER BY b.created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $booking_id = $booking['booking_id'];
            $hotel_id = $booking['hotel_id'];
        } else {
            // User has no bookings yet, just thank them
            echo json_encode([
                'success' => true, 
                'message' => 'Thank you for your feedback! We appreciate your support.'
            ]);
            exit;
        }
    }
    
    // Check if user already reviewed this
    $stmt = $conn->prepare("
        SELECT review_id FROM reviews 
        WHERE user_id = ? AND booking_id = ?
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you! You have already rated this service.'
        ]);
        exit;
    }
    
    // Insert review
    $title = "StayBnB Service Rating";
    $stmt = $conn->prepare("
        INSERT INTO reviews (booking_id, user_id, hotel_id, rating, title, comment, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'approved')
    ");
    $stmt->bind_param("iiidss", $booking_id, $_SESSION['user_id'], $hotel_id, $rating, $title, $comment);
    
    if ($stmt->execute()) {
        // Update hotel rating
        $conn->query("
            UPDATE hotels h
            SET h.star_rating = (
                SELECT COALESCE(AVG(r.rating), h.star_rating)
                FROM reviews r
                WHERE r.hotel_id = h.hotel_id AND r.status = 'approved'
            )
            WHERE h.hotel_id = $hotel_id
        ");
        
        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit rating']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>