<?php
/**
 * StayBnB - My Bookings (Complete History)
 * Copy this to: my-bookings.php (CREATE NEW)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

require_login();

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'all';

// Build query
$query = "SELECT b.*, h.name as hotel_name, h.location, hi.image_url
          FROM bookings b
          JOIN hotels h ON b.hotel_id = h.hotel_id
          LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
          WHERE b.user_id = ?";

if ($status_filter !== 'all') {
    $query .= " AND b.status = ?";
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bind_param("is", $_SESSION['user_id'], $status_filter);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; }
        
        .page-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .filter-tabs {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .filter-btn {
            border: none;
            background: transparent;
            padding: 10px 20px;
            border-radius: 20px;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #0a53fe;
            color: white;
        }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .booking-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-confirmed { background: #d1fae5; color: #059669; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .status-checked_in { background: #dbeafe; color: #0369a1; }
        .status-checked_out { background: #e5e7eb; color: #6b7280; }
        
        @media (max-width: 768px) {
            .booking-image { width: 100%; height: 200px; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hotel"></i> StayBnB
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="my-bookings.php">My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt me-2"></i>My Bookings</h1>
            <p class="mb-0">View and manage all your reservations</p>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <div class="text-center">
                <a href="my-bookings.php?status=all" 
                   class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
                    All Bookings
                </a>
                <a href="my-bookings.php?status=confirmed" 
                   class="filter-btn <?= $status_filter === 'confirmed' ? 'active' : '' ?>">
                    Confirmed
                </a>
                <a href="my-bookings.php?status=pending" 
                   class="filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">
                    Pending
                </a>
                <a href="my-bookings.php?status=cancelled" 
                   class="filter-btn <?= $status_filter === 'cancelled' ? 'active' : '' ?>">
                    Cancelled
                </a>
                <a href="my-bookings.php?status=checked_out" 
                   class="filter-btn <?= $status_filter === 'checked_out' ? 'active' : '' ?>">
                    Completed
                </a>
            </div>
        </div>

        <!-- Bookings List -->
        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($booking = $bookings->fetch_assoc()): ?>
            <div class="booking-card">
                <div class="row g-0">
                    <div class="col-md-3">
                        <img src="<?= htmlspecialchars($booking['image_url'] ?? 'assets/images/default-hotel.jpg') ?>" 
                             class="booking-image w-100" 
                             alt="<?= htmlspecialchars($booking['hotel_name']) ?>">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($booking['hotel_name']) ?></h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($booking['location']) ?>
                                    </p>
                                </div>
                                <span class="status-badge status-<?= $booking['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                </span>
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Booking Ref</small>
                                    <strong><?= htmlspecialchars($booking['booking_ref']) ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Check-in</small>
                                    <strong><?= format_date($booking['checkin_date'], 'M j, Y') ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Check-out</small>
                                    <strong><?= format_date($booking['checkout_date'], 'M j, Y') ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Guests</small>
                                    <strong><?= $booking['num_guests'] ?> guest(s)</strong>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block">Total Amount</small>
                                    <h5 class="mb-0 text-primary">₱<?= number_format($booking['total_amount']) ?></h5>
                                </div>
                                <div>
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <a href="view-eticket.php?ref=<?= $booking['booking_ref'] ?>" 
                                           class="btn btn-sm btn-primary me-2">
                                            <i class="fas fa-ticket-alt me-1"></i>View E-Ticket
                                        </a>
                                        <?php 
                                        $checkin_date = strtotime($booking['checkin_date']);
                                        $now = strtotime(date('Y-m-d'));
                                        $days_until_checkin = ($checkin_date - $now) / (60 * 60 * 24);
                                        
                                        if ($days_until_checkin > 1): 
                                        ?>
                                        <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($booking['status'] === 'pending'): ?>
                                        <a href="booking-confirmation.php?ref=<?= $booking['booking_ref'] ?>" 
                                           class="btn btn-sm btn-warning me-2">
                                            <i class="fas fa-credit-card me-1"></i>Complete Payment
                                        </a>
                                        <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Cancel this booking?')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                        
                                    <?php elseif ($booking['status'] === 'checked_out'): ?>
                                        <a href="hotel-details.php?id=<?= $booking['hotel_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-redo me-1"></i>Book Again
                                        </a>
                                        
                                    <?php elseif ($booking['status'] === 'cancelled'): ?>
                                        <span class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Cancelled on <?= format_date($booking['cancelled_at'] ?? $booking['updated_at']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4>No bookings found</h4>
                <p class="text-muted mb-4">
                    <?php if ($status_filter !== 'all'): ?>
                        You don't have any <?= $status_filter ?> bookings.
                    <?php else: ?>
                        You haven't made any bookings yet.
                    <?php endif; ?>
                </p>
                <a href="search.php" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Search Hotels
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>