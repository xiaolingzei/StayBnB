<?php
/**
 * StayBnB - Update Booking Status Handler
 * Location: admin/php/update_booking.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $new_status = sanitize_input($_POST['status'] ?? '');
    
    // Validation
    if ($booking_id <= 0) {
        redirect('../bookings.php', 'Invalid booking ID', 'error');
    }
    
    $valid_statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        redirect('../bookings.php', 'Invalid status', 'error');
    }
    
    // Get current booking details
    $stmt = $conn->prepare("
        SELECT b.*, h.name as hotel_name, u.email as user_email 
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.hotel_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        redirect('../bookings.php', 'Booking not found', 'error');
    }
    
    $booking = $result->fetch_assoc();
    $old_status = $booking['status'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE booking_id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    
    if ($stmt->execute()) {
        // If status changed to cancelled, update cancelled_at
        if ($new_status === 'cancelled' && $old_status !== 'cancelled') {
            $conn->query("UPDATE bookings SET cancelled_at = NOW() WHERE booking_id = $booking_id");
        }
        
        // Log activity
        log_activity($conn, 'booking_status_updated', 'bookings', $booking_id);
        
        // Optional: Send email notification to user
        // send_email($booking['user_email'], "Booking Status Updated", "Your booking {$booking['booking_ref']} status has been updated to: $new_status");
        
        redirect('../bookings.php', "Booking status updated from '$old_status' to '$new_status'", 'success');
    } else {
        error_log("Update booking error: " . $conn->error);
        redirect('../bookings.php', 'Error updating booking status', 'error');
    }
} else {
    redirect('../bookings.php', 'Invalid request method', 'error');
}

$stmt->close();
$conn->close();
?>