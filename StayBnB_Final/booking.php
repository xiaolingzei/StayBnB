<?php
/**
 * StayBnB - Booking Form
 * Copy this to: booking.php (REPLACE booking.html)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Require login
require_login('login.php?redirect=booking.php');

$user = get_current_user($conn);
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$error = '';

// Get hotel details
if ($hotel_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM hotels WHERE hotel_id = ? AND status = 'active'");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $hotel_result = $stmt->get_result();
    
    if ($hotel_result->num_rows === 0) {
        redirect('index.php', 'Hotel not found or unavailable', 'error');
    }
    
    $hotel = $hotel_result->fetch_assoc();
    $stmt->close();
} else {
    redirect('index.php', 'Please select a hotel', 'error');
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkin = sanitize_input($_POST['checkin'] ?? '');
    $checkout = sanitize_input($_POST['checkout'] ?? '');
    $guests = intval($_POST['guests'] ?? 1);
    $special_requests = sanitize_input($_POST['special_requests'] ?? '');
    
    // Validation
    if (empty($checkin) || empty($checkout)) {
        $error = 'Please select check-in and check-out dates';
    } elseif (strtotime($checkin) < strtotime(date('Y-m-d'))) {
        $error = 'Check-in date cannot be in the past';
    } elseif (strtotime($checkout) <= strtotime($checkin)) {
        $error = 'Check-out date must be after check-in date';
    } elseif ($guests < 1) {
        $error = 'Number of guests must be at least 1';
    } else {
        // Calculate nights and total
        $nights = calculate_nights($checkin, $checkout);
        $total_amount = $nights * $hotel['price_per_night'];
        
        // Generate booking reference
        $booking_ref = generate_booking_ref();
        
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (booking_ref, user_id, hotel_id, guest_fullname, guest_email, guest_phone, 
                                checkin_date, checkout_date, num_guests, num_nights, room_rate, total_amount, 
                                status, payment_status, special_requests, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, NOW())");
        
        $stmt->bind_param("siisssssidds", 
            $booking_ref, 
            $_SESSION['user_id'],
            $hotel_id,
            $user['fullname'],
            $user['email'],
            $user['phone'],
            $checkin,
            $checkout,
            $guests,
            $nights,
            $hotel['price_per_night'],
            $total_amount,
            $special_requests
        );
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            
            // Log activity
            log_activity($conn, 'booking_created', 'bookings', $booking_id);
            
            // Redirect to payment or confirmation
            redirect("booking-confirmation.php?ref={$booking_ref}", 
                    'Booking created successfully!', 'success');
        } else {
            $error = 'Failed to create booking. Please try again.';
            error_log("Booking error: " . $conn->error);
        }
        
        $stmt->close();
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?= htmlspecialchars($hotel['name']) ?> - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .booking-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px;
            margin: 40px 0;
        }
        
        .hotel-info {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .price-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        
        .form-control:focus {
            border-color: #0a53fe;
            box-shadow: 0 0 0 0.2rem rgba(10, 83, 254, 0.25);
        }
        
        .btn-book {
            background: #0a53fe;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .btn-book:hover {
            background: #1e40af;
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
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <a href="hotel-details.php?id=<?= $hotel_id ?>" class="btn btn-outline-secondary mt-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Hotel
                </a>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show mt-3">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Booking Form -->
            <div class="col-lg-8">
                <div class="booking-container">
                    <!-- Hotel Info -->
                    <div class="hotel-info">
                        <h2 class="mb-3"><?= htmlspecialchars($hotel['name']) ?></h2>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?= htmlspecialchars($hotel['location']) ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            <?= number_format($hotel['star_rating'], 1) ?> Stars
                        </p>
                    </div>

                    <h4 class="mb-4">Complete Your Booking</h4>

                    <form method="POST" action="booking.php?hotel_id=<?= $hotel_id ?>" id="bookingForm">
                        <!-- Guest Information -->
                        <div class="mb-4">
                            <h5 class="mb-3">Guest Information</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($user['fullname']) ?>" 
                                           readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>" 
                                           readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                           readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="mb-4">
                            <h5 class="mb-3">Booking Details</h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Check-in Date *</label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="checkin" 
                                           id="checkin"
                                           min="<?= date('Y-m-d') ?>"
                                           value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>"
                                           required
                                           onchange="calculateTotal()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-out Date *</label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="checkout" 
                                           id="checkout"
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                           value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>"
                                           required
                                           onchange="calculateTotal()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Guests *</label>
                                    <select class="form-select" name="guests" required>
                                        <option value="1">1 Guest</option>
                                        <option value="2" selected>2 Guests</option>
                                        <option value="3">3 Guests</option>
                                        <option value="4">4 Guests</option>
                                        <option value="5">5 Guests</option>
                                        <option value="6">6+ Guests</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div class="mb-4">
                            <label class="form-label">Special Requests (Optional)</label>
                            <textarea class="form-control" 
                                      name="special_requests" 
                                      rows="3" 
                                      placeholder="Any special requirements? (e.g., early check-in, room preferences)"><?= htmlspecialchars($_POST['special_requests'] ?? '') ?></textarea>
                        </div>

                        <!-- Terms -->
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#">cancellation policy</a> and <a href="#">terms & conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-book w-100">
                            <i class="fas fa-check-circle me-2"></i>Confirm Booking
                        </button>
                    </form>
                </div>
            </div>

            <!-- Price Summary -->
            <div class="col-lg-4">
                <div class="price-summary">
                    <h5 class="mb-4">Price Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Price per night:</span>
                        <strong>₱<?= number_format($hotel['price_per_night']) ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2" id="nightsDisplay" style="display:none!important">
                        <span>Number of nights:</span>
                        <strong><span id="nights">0</span> night(s)</strong>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5 mb-0">Total Amount:</span>
                        <span class="h5 mb-0 text-primary" id="totalAmount">₱0</span>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Payment will be processed after booking confirmation</small>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="mb-3">Included in your booking:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Free WiFi
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Complimentary breakfast
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>24/7 customer support
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>Free cancellation (24hrs before)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pricePerNight = <?= $hotel['price_per_night'] ?>;
        
        function calculateTotal() {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            if (checkin && checkout) {
                const start = new Date(checkin);
                const end = new Date(checkout);
                const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    const total = nights * pricePerNight;
                    document.getElementById('nights').textContent = nights;
                    document.getElementById('totalAmount').textContent = '₱' + total.toLocaleString();
                    document.getElementById('nightsDisplay').style.display = 'flex';
                } else {
                    document.getElementById('nightsDisplay').style.display = 'none';
                    document.getElementById('totalAmount').textContent = '₱0';
                }
            }
        }
        
        // Update checkout min date when checkin changes
        document.getElementById('checkin').addEventListener('change', function() {
            const checkin = new Date(this.value);
            checkin.setDate(checkin.getDate() + 1);
            const minCheckout = checkin.toISOString().split('T')[0];
            document.getElementById('checkout').setAttribute('min', minCheckout);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>