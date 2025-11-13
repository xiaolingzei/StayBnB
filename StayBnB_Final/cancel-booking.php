<?php
define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';
require_login();

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id > 0) {
    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Check if cancellation is allowed (24hrs before checkin)
        $checkin_timestamp = strtotime($booking['checkin_date']);
        $now = time();
        $hours_until_checkin = ($checkin_timestamp - $now) / 3600;
        
        if ($hours_until_checkin >= 24) {
            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled', cancelled_at = NOW() WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
            log_activity($conn, 'booking_cancelled', 'bookings', $booking_id);
            
            redirect('my-bookings.php', 'Booking cancelled successfully', 'success');
        } else {
            redirect('my-bookings.php', 'Cancellation must be made at least 24 hours before check-in', 'error');
        }
    } else {
        redirect('my-bookings.php', 'Booking not found', 'error');
    }
} else {
    redirect('my-bookings.php', 'Invalid booking ID', 'error');
}

$conn->close();
?>