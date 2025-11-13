<?php
/**
 * StayBnB - Delete Hotel Handler
 * Location: admin/php/delete_hotel.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hotel_id <= 0) {
    redirect('../hotels.php', 'Invalid hotel ID', 'error');
}

// Check if hotel exists
$stmt = $conn->prepare("SELECT name FROM hotels WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('../hotels.php', 'Hotel not found', 'error');
}

$hotel = $result->fetch_assoc();
$stmt->close();

// Check if hotel has active bookings
$stmt = $conn->prepare("
    SELECT COUNT(*) as booking_count 
    FROM bookings 
    WHERE hotel_id = ? 
    AND status IN ('pending', 'confirmed', 'checked_in')
");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$booking_data = $result->fetch_assoc();

if ($booking_data['booking_count'] > 0) {
    redirect('../hotels.php', 'Cannot delete hotel with active bookings. Please cancel bookings first.', 'error');
}

// Delete hotel (CASCADE will delete related images, reviews, etc.)
$stmt = $conn->prepare("DELETE FROM hotels WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);

if ($stmt->execute()) {
    // Log activity
    log_activity($conn, 'hotel_deleted', 'hotels', $hotel_id);
    
    redirect('../hotels.php', "Hotel '{$hotel['name']}' deleted successfully", 'success');
} else {
    error_log("Delete hotel error: " . $conn->error);
    redirect('../hotels.php', 'Error deleting hotel. Please try again.', 'error');
}

$stmt->close();
$conn->close();
?>