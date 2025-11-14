<?php
/**
 * StayBnB - Write Review Page
 * CREATE NEW FILE: write-review.php
 * Beautiful review form with star rating
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

require_login();

$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;

if ($hotel_id <= 0) {
    redirect('dashboard.php', 'Invalid hotel', 'error');
}

// Get hotel details
$stmt = $conn->prepare("
    SELECT h.*, hi.image_url 
    FROM hotels h
    LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
    WHERE h.hotel_id = ?
");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php', 'Hotel not found', 'error');
}

$hotel = $result->fetch_assoc();

// Check if user has booked this hotel
$stmt = $conn->prepare("
    SELECT booking_id, booking_ref, checkin_date, checkout_date 
    FROM bookings 
    WHERE user_id = ? AND hotel_id = ? 
    AND status IN ('checked_out', 'confirmed')
    ORDER BY checkout_date DESC 
    LIMIT 1
");
$stmt->bind_param("ii", $_SESSION['user_id'], $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('hotel-details.php?id=' . $hotel_id, 'You must book this hotel before reviewing', 'error');
}

$booking = $result->fetch_assoc();

// Check if already reviewed
$stmt = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND hotel_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    redirect('hotel-details.php?id=' . $hotel_id, 'You have already reviewed this hotel', 'info');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - <?= htmlspecialchars($hotel['name']) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; }
        
        .review-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .review-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto 40px;
        }
        
        .hotel-info {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .hotel-thumb {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .star-rating {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 30px 0;
        }
        
        .star-rating i {
            font-size: 3rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .star-rating i:hover,
        .star-rating i.active {
            color: #fbbf24;
            transform: scale(1.2);
        }
        
        .rating-text {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: #0a53fe;
            margin-bottom: 30px;
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
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="review-header">
        <div class="container text-center">
            <h1><i class="fas fa-star me-2"></i>Write a Review</h1>
            <p class="mb-0">Share your experience with other travelers</p>
        </div>
    </div>

    <div class="container">
        <div class="review-card">
            <!-- Hotel Info -->
            <div class="hotel-info">
                <img src="<?= htmlspecialchars($hotel['image_url']) ?>" 
                     class="hotel-thumb" 
                     alt="<?= htmlspecialchars($hotel['name']) ?>">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($hotel['name']) ?></h5>
                    <p class="text-muted mb-1">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= htmlspecialchars($hotel['location']) ?>
                    </p>
                    <small class="text-muted">
                        Your stay: <?= date('M j', strtotime($booking['checkin_date'])) ?> - 
                        <?= date('M j, Y', strtotime($booking['checkout_date'])) ?>
                    </small>
                </div>
            </div>

            <h4 class="text-center mb-4">How was your experience?</h4>

            <!-- Review Form -->
            <form id="reviewForm">
                <input type="hidden" name="hotel_id" value="<?= $hotel_id ?>">
                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                <input type="hidden" id="ratingValue" name="rating" value="0">

                <!-- Star Rating -->
                <div class="star-rating" id="starRating">
                    <i class="far fa-star" data-rating="1"></i>
                    <i class="far fa-star" data-rating="2"></i>
                    <i class="far fa-star" data-rating="3"></i>
                    <i class="far fa-star" data-rating="4"></i>
                    <i class="far fa-star" data-rating="5"></i>
                </div>

                <div class="rating-text" id="ratingText">Click a star to rate</div>

                <!-- Review Title -->
                <div class="mb-3">
                    <label class="form-label">Review Title (Optional)</label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="Summarize your experience">
                </div>

                <!-- Review Comment -->
                <div class="mb-3">
                    <label class="form-label">Your Review *</label>
                    <textarea name="comment" class="form-control" rows="6" 
                              placeholder="Share details about your stay. What did you like? What could be improved?" 
                              required></textarea>
                    <small class="text-muted">Minimum 20 characters</small>
                </div>

                <!-- Guidelines -->
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle me-2"></i>Review Guidelines:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li>Be honest and fair</li>
                        <li>Focus on your personal experience</li>
                        <li>Avoid offensive language</li>
                        <li>Don't share personal contact information</li>
                    </ul>
                </div>

                <div id="messageArea"></div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const stars = document.querySelectorAll('#starRating i');
        const ratingValue = document.getElementById('ratingValue');
        const ratingText = document.getElementById('ratingText');
        
        const ratingTexts = {
            1: '⭐ Poor - Not recommended',
            2: '⭐⭐ Fair - Below expectations',
            3: '⭐⭐⭐ Good - Met expectations',
            4: '⭐⭐⭐⭐ Very Good - Exceeded expectations',
            5: '⭐⭐⭐⭐⭐ Excellent - Outstanding!'
        };

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                ratingValue.value = rating;
                ratingText.textContent = ratingTexts[rating];
                
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.className = 'fas fa-star active';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.className = 'fas fa-star';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            const currentRating = ratingValue.value;
            stars.forEach((s, index) => {
                if (index < currentRating) {
                    s.className = 'fas fa-star active';
                } else {
                    s.className = 'far fa-star';
                }
            });
        });

        document.getElementById('reviewForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const rating = ratingValue.value;
            if (rating === '0') {
                document.getElementById('messageArea').innerHTML = 
                    '<div class="alert alert-warning">Please select a star rating</div>';
                return;
            }
            
            const comment = this.comment.value.trim();
            if (comment.length < 20) {
                document.getElementById('messageArea').innerHTML = 
                    '<div class="alert alert-warning">Review must be at least 20 characters</div>';
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('submit-review.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('messageArea').innerHTML = 
                        '<div class="alert alert-success">' + data.message + '</div>';
                    
                    setTimeout(() => {
                        window.location.href = 'hotel-details.php?id=<?= $hotel_id ?>';
                    }, 2000);
                } else {
                    document.getElementById('messageArea').innerHTML = 
                        '<div class="alert alert-danger">' + data.message + '</div>';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Review';
                }
            } catch (error) {
                document.getElementById('messageArea').innerHTML = 
                    '<div class="alert alert-danger">Error submitting review. Please try again.</div>';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Review';
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>