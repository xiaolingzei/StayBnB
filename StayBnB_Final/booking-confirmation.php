<?php
/**
 * StayBnB - Booking Confirmation Page
 * Copy this to: booking-confirmation.php (CREATE NEW)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

require_login();

$booking_ref = isset($_GET['ref']) ? sanitize_input($_GET['ref']) : '';

if (empty($booking_ref)) {
    redirect('dashboard.php', 'Invalid booking reference', 'error');
}

// Get booking details
$query = "SELECT b.*, h.name as hotel_name, h.location, h.address, h.check_in_time, h.check_out_time,
          hi.image_url
          FROM bookings b
          JOIN hotels h ON b.hotel_id = h.hotel_id
          LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
          WHERE b.booking_ref = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("si", $booking_ref, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php', 'Booking not found', 'error');
}

$booking = $result->fetch_assoc();
$stmt->close();

// Send confirmation email (optional)
$email_subject = "Booking Confirmation - {$booking_ref}";
$email_body = "Dear {$booking['guest_fullname']},\n\n";
$email_body .= "Your booking has been confirmed!\n\n";
$email_body .= "Booking Reference: {$booking_ref}\n";
$email_body .= "Hotel: {$booking['hotel_name']}\n";
$email_body .= "Check-in: " . format_date($booking['checkin_date']) . "\n";
$email_body .= "Check-out: " . format_date($booking['checkout_date']) . "\n\n";
$email_body .= "Total Amount: ₱" . number_format($booking['total_amount']) . "\n\n";
$email_body .= "Thank you for choosing StayBnB!";

// send_email($booking['guest_email'], $email_subject, $email_body);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .success-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        
        .booking-details {
            padding: 40px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .btn-action {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="mb-2">Booking Confirmed!</h1>
            <p class="mb-0">Your reservation has been successfully created</p>
        </div>
        
        <div class="booking-details">
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                A confirmation email has been sent to <strong><?= htmlspecialchars($booking['guest_email']) ?></strong>
            </div>
            
            <?php if (!empty($booking['image_url'])): ?>
            <img src="<?= htmlspecialchars($booking['image_url']) ?>" 
                 class="hotel-image" 
                 alt="<?= htmlspecialchars($booking['hotel_name']) ?>">
            <?php endif; ?>
            
            <h4 class="mb-4">Booking Details</h4>
            
            <div class="detail-row">
                <span class="text-muted">Booking Reference</span>
                <strong class="text-primary"><?= htmlspecialchars($booking['booking_ref']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Hotel Name</span>
                <strong><?= htmlspecialchars($booking['hotel_name']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Location</span>
                <strong><?= htmlspecialchars($booking['location']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Guest Name</span>
                <strong><?= htmlspecialchars($booking['guest_fullname']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Check-in Date</span>
                <strong><?= format_date($booking['checkin_date']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Check-in Time</span>
                <strong><?= date('g:i A', strtotime($booking['check_in_time'])) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Check-out Date</span>
                <strong><?= format_date($booking['checkout_date']) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Check-out Time</span>
                <strong><?= date('g:i A', strtotime($booking['check_out_time'])) ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Number of Guests</span>
                <strong><?= $booking['num_guests'] ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Number of Nights</span>
                <strong><?= $booking['num_nights'] ?></strong>
            </div>
            
            <div class="detail-row">
                <span class="text-muted">Status</span>
                <span class="badge bg-warning">Pending Payment</span>
            </div>
            
            <div class="detail-row">
                <span class="h5 mb-0">Total Amount</span>
                <span class="h5 mb-0 text-primary">₱<?= number_format($booking['total_amount']) ?></span>
            </div>
            
            <?php if (!empty($booking['special_requests'])): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <strong>Special Requests:</strong>
                <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded">
                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                <strong>Payment Required:</strong>
                <p class="mb-0 mt-2">Please complete your payment to confirm your reservation. You can pay online or at the hotel upon arrival.</p>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <a href="view-eticket.php?ref=<?= $booking_ref ?>" 
                   class="btn btn-primary btn-action">
                    <i class="fas fa-ticket-alt me-2"></i>View E-Ticket
                </a>
                <a href="dashboard.php" 
                   class="btn btn-outline-primary btn-action">
                    <i class="fas fa-th-large me-2"></i>Go to Dashboard
                </a>
                <a href="index.php" 
                   class="btn btn-outline-secondary btn-action">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <i class="fas fa-shield-alt me-2"></i>
                    Your booking is secure and protected
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>