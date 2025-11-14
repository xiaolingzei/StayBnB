<?php
/**
 * FIXED dashboard.php - Replace your existing dashboard.php with this
 * Fixes: "Booking not found" error and number_format() null error
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Require login
require_login();

$user = get_user_data($conn);

// Get user's bookings
$bookings_query = "SELECT b.*, h.name as hotel_name, h.location, hi.image_url,
                   DATEDIFF(b.checkout_date, b.checkin_date) as nights
                   FROM bookings b
                   JOIN hotels h ON b.hotel_id = h.hotel_id
                   LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
                   WHERE b.user_id = ?
                   ORDER BY b.created_at DESC
                   LIMIT 10";

$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();

// Get booking statistics - FIX: Handle null values
$stats_query = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                COALESCE(SUM(total_amount), 0) as total_spent
                FROM bookings
                WHERE user_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// FIX: Ensure total_spent is never null
$stats['total_spent'] = floatval($stats['total_spent'] ?? 0);

// Get recommendations
$rec_response = @file_get_contents(SITE_URL . "api/get_recommendations.php?user_id=" . $_SESSION['user_id']);
$recommendations = [];
if ($rec_response) {
    $rec_data = json_decode($rec_response, true);
    if ($rec_data && isset($rec_data['success']) && $rec_data['success']) {
        $recommendations = array_slice($rec_data['recommendations'], 0, 3);
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .dashboard-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stat-icon.blue { background: #e0f2fe; color: #0369a1; }
        .stat-icon.green { background: #d1fae5; color: #059669; }
        .stat-icon.yellow { background: #fef3c7; color: #d97706; }
        .stat-icon.red { background: #fee2e2; color: #dc2626; }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .booking-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="mb-2">
                <i class="fas fa-th-large me-2"></i>Welcome back, <?= htmlspecialchars($user['fullname']) ?>!
            </h1>
            <p class="mb-0">Manage your bookings and explore new destinations</p>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="container mb-4">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mb-5">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1"><?= intval($stats['total_bookings']) ?></h3>
                    <p class="text-muted mb-0">Total Bookings</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1"><?= intval($stats['confirmed']) ?></h3>
                    <p class="text-muted mb-0">Confirmed</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1"><?= intval($stats['pending']) ?></h3>
                    <p class="text-muted mb-0">Pending</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3 class="mb-1">₱<?= number_format($stats['total_spent'], 0) ?></h3>
                    <p class="text-muted mb-0">Total Spent</p>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">
                        <i class="fas fa-list me-2"></i>Recent Bookings
                    </h3>
                    <a href="my-bookings.php" class="btn btn-outline-primary">
                        View All <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
                
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
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="card-title mb-1"><?= htmlspecialchars($booking['hotel_name']) ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?= htmlspecialchars($booking['location']) ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Booking Ref</small>
                                            <strong><?= htmlspecialchars($booking['booking_ref']) ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Check-in</small>
                                            <strong><?= format_date($booking['checkin_date']) ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Check-out</small>
                                            <strong><?= format_date($booking['checkout_date']) ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Total Amount</small>
                                            <h5 class="mb-0 text-primary">₱<?= number_format($booking['total_amount']) ?></h5>
                                        </div>
                                        <div>
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <a href="view-eticket.php?ref=<?= $booking['booking_ref'] ?>" 
                                                   class="btn btn-sm btn-primary me-2">
                                                    <i class="fas fa-ticket-alt me-1"></i>View E-Ticket
                                                </a>
                                                <a href="cancel-booking.php?id=<?= $booking['booking_id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    <i class="fas fa-times me-1"></i>Cancel
                                                </a>
                                            <?php elseif ($booking['status'] === 'pending'): ?>
                                                <a href="booking.php?id=<?= $booking['booking_id'] ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-credit-card me-1"></i>Complete Payment
                                                </a>
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
                        <h5>No bookings yet</h5>
                        <p class="text-muted">Start exploring and book your first hotel!</p>
                        <a href="search.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Search Hotels
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recommendations Sidebar -->
            <div class="col-lg-4">
                <h4 class="mb-4">
                    <i class="fas fa-star me-2"></i>Recommended for You
                </h4>
                
                <?php if (!empty($recommendations)): ?>
                    <?php foreach ($recommendations as $hotel): ?>
                    <div class="card mb-3">
                        <img src="<?= htmlspecialchars($hotel['image_url']) ?>" 
                             class="card-img-top" 
                             style="height: 150px; object-fit: cover;"
                             alt="<?= htmlspecialchars($hotel['name']) ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($hotel['name']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($hotel['location']) ?>
                            </p>
                            <?php if (!empty($hotel['reasons'])): ?>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-lightbulb me-1"></i>
                                <?= htmlspecialchars($hotel['reasons'][0]) ?>
                            </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">₱<?= number_format($hotel['price_per_night']) ?></span>
                                <a href="hotel-details.php?id=<?= $hotel['hotel_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">Book your first hotel to get personalized recommendations!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- FIXED Rating Modal - No more "Booking not found" error -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-star me-2"></i>Rate Our Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">How was your experience with StayBnB?</p>
                
                <div class="rating-stars-large mb-4" id="ratingStars">
                    <i class="far fa-star" data-rating="1"></i>
                    <i class="far fa-star" data-rating="2"></i>
                    <i class="far fa-star" data-rating="3"></i>
                    <i class="far fa-star" data-rating="4"></i>
                    <i class="far fa-star" data-rating="5"></i>
                </div>
                
                <input type="hidden" id="selectedRating" value="0">
                
                <div class="mb-3">
                    <textarea id="ratingComment" class="form-control" rows="3" placeholder="Tell us more about your experience (optional)"></textarea>
                </div>
                
                <div id="ratingMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Maybe Later</button>
                <button type="button" class="btn btn-primary" onclick="submitRating()">Submit Rating</button>
            </div>
        </div>
    </div>
</div>

<style>
.rating-stars-large i {
    font-size: 3rem;
    color: #fbbf24;
    cursor: pointer;
    transition: all 0.3s;
    margin: 0 5px;
}

.rating-stars-large i:hover,
.rating-stars-large i.active {
    color: #f59e0b;
    transform: scale(1.2);
}
</style>

<script>
// FIXED: Show rating modal only if user has bookings
<?php if ($stats['total_bookings'] > 0): ?>
setTimeout(() => {
    const hasRated = localStorage.getItem('hasRatedStayBnB');
    if (!hasRated) {
        new bootstrap.Modal(document.getElementById('ratingModal')).show();
    }
}, 10000);
<?php endif; ?>

// Star rating interaction
document.querySelectorAll('#ratingStars i').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('selectedRating').value = rating;
        
        document.querySelectorAll('#ratingStars i').forEach((s, index) => {
            if (index < rating) {
                s.className = 'fas fa-star active';
            } else {
                s.className = 'far fa-star';
            }
        });
    });
});

async function submitRating() {
    const rating = document.getElementById('selectedRating').value;
    const comment = document.getElementById('ratingComment').value;
    
    if (rating == 0) {
        document.getElementById('ratingMessage').innerHTML = '<div class="alert alert-warning">Please select a rating</div>';
        return;
    }
    
    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('comment', comment);
    formData.append('booking_id', 0); // FIXED: General service rating, not specific booking
    formData.append('hotel_id', 0); // FIXED: Service rating
    
    try {
        const response = await fetch('rate-service.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            localStorage.setItem('hasRatedStayBnB', 'true');
            document.getElementById('ratingMessage').innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('ratingModal')).hide();
            }, 2000);
        } else {
            document.getElementById('ratingMessage').innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
        }
    } catch (error) {
        document.getElementById('ratingMessage').innerHTML = '<div class="alert alert-danger">Error submitting rating. Please try again.</div>';
    }
}
</script>

</body>
</html>
<?php $conn->close(); ?>