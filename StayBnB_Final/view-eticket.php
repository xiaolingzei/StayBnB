<?php
/**
 * StayBnB - E-Ticket Display
 * Copy this to: view-eticket.php (CREATE NEW)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

require_login();

$booking_ref = isset($_GET['ref']) ? sanitize_input($_GET['ref']) : '';

if (empty($booking_ref)) {
    redirect('dashboard.php', 'Invalid booking reference', 'error');
}

// Get booking details with hotel info
$query = "SELECT b.*, h.name as hotel_name, h.location, h.address, h.phone as hotel_phone,
          h.check_in_time, h.check_out_time, hi.image_url
          FROM bookings b
          JOIN hotels h ON b.hotel_id = h.hotel_id
          LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
          WHERE b.booking_ref = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("si", $booking_ref, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php', 'E-Ticket not found', 'error');
}

$booking = $result->fetch_assoc();
$stmt->close();

// Generate QR code data
$qr_data = json_encode([
    'ref' => $booking['booking_ref'],
    'hotel_id' => $booking['hotel_id'],
    'checkin' => $booking['checkin_date'],
    'guest' => $booking['guest_fullname']
]);

$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - <?= htmlspecialchars($booking['booking_ref']) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; }
        
        .eticket-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .eticket-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .eticket-body {
            padding: 40px;
        }
        
        .hotel-banner {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .booking-ref {
            font-size: 2rem;
            font-weight: 700;
            color: #0a53fe;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }
        
        .detail-section {
            margin-bottom: 30px;
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
        
        .qr-code-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 30px 0;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 20px auto;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .eticket-container { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
    <!-- Navigation (No Print) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hotel"></i> StayBnB
            </a>
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="eticket-container">
        <!-- E-Ticket Header -->
        <div class="eticket-header">
            <h1 class="mb-2">
                <i class="fas fa-ticket-alt me-2"></i>E-TICKET
            </h1>
            <p class="mb-0">StayBnB Booking Confirmation</p>
        </div>
        
        <!-- E-Ticket Body -->
        <div class="eticket-body">
            <?php if (!empty($booking['image_url'])): ?>
            <img src="<?= htmlspecialchars($booking['image_url']) ?>" 
                 class="hotel-banner" 
                 alt="<?= htmlspecialchars($booking['hotel_name']) ?>">
            <?php endif; ?>
            
            <!-- Booking Reference -->
            <div class="booking-ref">
                <?= htmlspecialchars($booking['booking_ref']) ?>
            </div>
            
            <!-- QR Code -->
            <div class="qr-code-section">
                <h5 class="mb-3">Scan for Quick Check-in</h5>
                <img src="<?= $qr_code_url ?>" class="qr-code" alt="QR Code">
                <p class="text-muted small mt-3">Present this QR code at hotel reception</p>
            </div>
            
            <!-- Guest Details -->
            <div class="detail-section">
                <h5 class="mb-3">Guest Information</h5>
                <div class="detail-row">
                    <span class="text-muted">Guest Name</span>
                    <strong><?= htmlspecialchars($booking['guest_fullname']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Email</span>
                    <strong><?= htmlspecialchars($booking['guest_email']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Phone</span>
                    <strong><?= htmlspecialchars($booking['guest_phone']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Number of Guests</span>
                    <strong><?= $booking['num_guests'] ?></strong>
                </div>
            </div>
            
            <!-- Hotel Details -->
            <div class="detail-section">
                <h5 class="mb-3">Hotel Information</h5>
                <div class="detail-row">
                    <span class="text-muted">Hotel Name</span>
                    <strong><?= htmlspecialchars($booking['hotel_name']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Location</span>
                    <strong><?= htmlspecialchars($booking['location']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Address</span>
                    <strong><?= htmlspecialchars($booking['address']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Hotel Contact</span>
                    <strong><?= htmlspecialchars($booking['hotel_phone'] ?? 'N/A') ?></strong>
                </div>
            </div>
            
            <!-- Booking Details -->
            <div class="detail-section">
                <h5 class="mb-3">Booking Details</h5>
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
                    <span class="text-muted">Number of Nights</span>
                    <strong><?= $booking['num_nights'] ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Booking Status</span>
                    <strong class="text-success"><?= ucfirst($booking['status']) ?></strong>
                </div>
            </div>
            
            <!-- Payment Details -->
            <div class="detail-section">
                <h5 class="mb-3">Payment Information</h5>
                <div class="detail-row">
                    <span class="text-muted">Room Rate (per night)</span>
                    <strong>₱<?= number_format($booking['room_rate']) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="text-muted">Payment Status</span>
                    <strong><?= ucfirst(str_replace('_', ' ', $booking['payment_status'])) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="h5 mb-0">Total Amount</span>
                    <span class="h5 mb-0 text-primary">₱<?= number_format($booking['total_amount']) ?></span>
                </div>
            </div>
            
            <?php if (!empty($booking['special_requests'])): ?>
            <div class="alert alert-info">
                <strong>Special Requests:</strong>
                <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Important Notice -->
            <div class="alert alert-warning mt-4">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Information</h6>
                <ul class="mb-0 small">
                    <li>Please bring a valid ID for verification at check-in</li>
                    <li>Early check-in/late check-out subject to availability</li>
                    <li>Cancellation must be made 24 hours before check-in</li>
                    <li>For inquiries, contact hotel directly or email support@staybnb.com</li>
                </ul>
            </div>
            
            <!-- Action Buttons (No Print) -->
            <div class="action-buttons no-print">
                <button onclick="window.print()" class="btn btn-primary flex-fill">
                    <i class="fas fa-print me-2"></i>Print E-Ticket
                </button>
                <a href="my-bookings.php" class="btn btn-outline-secondary flex-fill">
                    <i class="fas fa-list me-2"></i>View All Bookings
                </a>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-4 pt-4 border-top">
                <p class="text-muted small mb-0">
                    Booking created on <?= format_datetime($booking['created_at']) ?>
                </p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-shield-alt me-1"></i>
                    This e-ticket is valid and secure
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>