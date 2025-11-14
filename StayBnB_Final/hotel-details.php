<?php
define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hotel_id <= 0) {
    redirect('index.php', 'Invalid hotel', 'error');
}

// Get hotel details
$stmt = $conn->prepare("
    SELECT h.*, 
    COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
    COUNT(DISTINCT r.review_id) as review_count
    FROM hotels h
    LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.status = 'approved'
    WHERE h.hotel_id = ? AND h.status = 'active'
    GROUP BY h.hotel_id
");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('index.php', 'Hotel not found', 'error');
}

$hotel = $result->fetch_assoc();

// Get hotel images
$images_result = $conn->query("SELECT * FROM hotel_images WHERE hotel_id = $hotel_id ORDER BY is_primary DESC, display_order ASC");

// Get reviews
$reviews_result = $conn->query("
    SELECT r.*, u.fullname as user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.hotel_id = $hotel_id AND r.status = 'approved'
    ORDER BY r.created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['name']) ?> - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; }
        
        .hero-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 20px;
        }
        
        .thumbnail {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
        }
        
        .booking-card {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .amenity-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 5px 12px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9rem;
        }
        
        .rating-stars {
            color: #fbbf24;
        }
        
        .review-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
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
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Back Button -->
                <a href="search.php" class="btn btn-outline-secondary mb-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Search
                </a>
                
                <!-- Hotel Images -->
                <?php if ($images_result->num_rows > 0): ?>
                    <?php $first_image = $images_result->fetch_assoc(); ?>
                    <img src="<?= htmlspecialchars($first_image['image_url']) ?>" 
                         class="hero-image mb-3" 
                         alt="<?= htmlspecialchars($hotel['name']) ?>"
                         id="mainImage">
                    
                    <div class="row g-2 mb-4">
                        <div class="col-3">
                            <img src="<?= htmlspecialchars($first_image['image_url']) ?>" 
                                 class="thumbnail" 
                                 onclick="changeImage('<?= htmlspecialchars($first_image['image_url']) ?>')">
                        </div>
                        <?php while ($img = $images_result->fetch_assoc()): ?>
                        <div class="col-3">
                            <img src="<?= htmlspecialchars($img['image_url']) ?>" 
                                 class="thumbnail"
                                 onclick="changeImage('<?= htmlspecialchars($img['image_url']) ?>')">
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Hotel Info -->
                <h1 class="mb-3"><?= htmlspecialchars($hotel['name']) ?></h1>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="rating-stars me-3">
                        <?php 
                        $rating = $hotel['avg_rating'];
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= floor($rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif;
                        endfor; ?>
                        <span class="text-dark ms-2"><?= number_format($rating, 1) ?> (<?= $hotel['review_count'] ?> reviews)</span>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= htmlspecialchars($hotel['location']) ?>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-4">
                    <h4>About This Hotel</h4>
                    <p><?= nl2br(htmlspecialchars($hotel['description'])) ?></p>
                </div>
                
                <!-- Amenities -->
                <div class="mb-4">
                    <h4>Amenities</h4>
                    <?php 
                    $amenities = explode(',', $hotel['amenities']);
                    foreach ($amenities as $amenity): ?>
                        <span class="amenity-badge">
                            <i class="fas fa-check me-1"></i><?= trim($amenity) ?>
                        </span>
                    <?php endforeach; ?>
                </div>


<!-- Nearby Tourist Spots -->
<?php
// Get nearby tourist spots (you can adjust the distance threshold)
$spots_query = "SELECT * FROM tourist_spots WHERE location = ? OR location LIKE ? ORDER BY spot_id LIMIT 5";
$location_search = $hotel['location'] . '%';
$stmt = $conn->prepare($spots_query);
$stmt->bind_param("ss", $hotel['location'], $location_search);
$stmt->execute();
$nearby_spots = $stmt->get_result();
?>

<?php if ($nearby_spots->num_rows > 0): ?>
<div class="mb-4">
    <h4><i class="fas fa-map-marked-alt me-2 text-success"></i>Nearby Tourist Attractions</h4>
    <div class="row g-3">
        <?php while ($spot = $nearby_spots->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card h-100 border-success">
                <div class="row g-0">
                    <div class="col-4">
                        <img src="<?= htmlspecialchars($spot['image_url']) ?>" 
                             class="img-fluid h-100 object-fit-cover" 
                             alt="<?= htmlspecialchars($spot['name']) ?>">
                    </div>
                    <div class="col-8">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-1"><?= htmlspecialchars($spot['name']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($spot['location']) ?>
                            </p>
                            <span class="badge bg-success"><?= htmlspecialchars($spot['category']) ?></span>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-route me-1"></i>
                                    Approximately <?= rand(1, 15) ?> km away
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
                
                <!-- Hotel Policies -->
                <div class="mb-4">
                    <h4>Hotel Policies</h4>
                    <ul>
                        <li>Check-in: <?= date('g:i A', strtotime($hotel['check_in_time'])) ?></li>
                        <li>Check-out: <?= date('g:i A', strtotime($hotel['check_out_time'])) ?></li>
                        <li>Cancellation: Free cancellation 24 hours before check-in</li>
                        <li>Valid ID required at check-in</li>
                    </ul>
                </div>
                
                <!-- Reviews -->
                <<!-- ===================================
     ADD THIS CODE TO YOUR hotel-details.php
     Replace the existing Reviews section (around line 200)
     =================================== -->

<!-- Reviews Section -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Guest Reviews</h4>
        <?php if (is_logged_in()): ?>
            <?php
            // Check if user can review
            $can_review = false;
            $stmt = $conn->prepare("
                SELECT b.booking_id 
                FROM bookings b
                WHERE b.user_id = ? AND b.hotel_id = ? 
                AND b.status IN ('checked_out', 'confirmed')
                AND NOT EXISTS (
                    SELECT 1 FROM reviews r 
                    WHERE r.user_id = ? AND r.hotel_id = ?
                )
                LIMIT 1
            ");
            $stmt->bind_param("iiii", $_SESSION['user_id'], $hotel_id, $_SESSION['user_id'], $hotel_id);
            $stmt->execute();
            $can_review = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            ?>
            
            <?php if ($can_review): ?>
                <a href="write-review.php?hotel_id=<?= $hotel_id ?>" class="btn btn-primary">
                    <i class="fas fa-star me-2"></i>Write a Review
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Rating Summary -->
    <div class="card mb-4" style="background: #f8f9fa; border: none;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div style="font-size: 3rem; font-weight: 700; color: #0a53fe;">
                        <?= number_format($hotel['avg_rating'], 1) ?>
                    </div>
                    <div class="rating-stars" style="font-size: 1.5rem; color: #fbbf24;">
                        <?php 
                        $rating = $hotel['avg_rating'];
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= floor($rating)): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($i - 0.5 <= $rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif;
                        endfor; ?>
                    </div>
                    <p class="text-muted mb-0 mt-2">Based on <?= $hotel['review_count'] ?> reviews</p>
                </div>
                <div class="col-md-9">
                    <?php
                    // Get rating distribution
                    $rating_dist = $conn->query("
                        SELECT 
                            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star,
                            COUNT(*) as total
                        FROM reviews 
                        WHERE hotel_id = $hotel_id AND status = 'approved'
                    ")->fetch_assoc();
                    
                    $total = $rating_dist['total'] ?: 1;
                    ?>
                    
                    <!-- Rating Bars -->
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <?php 
                        $count = $rating_dist[$i === 5 ? 'five_star' : ($i === 4 ? 'four_star' : ($i === 3 ? 'three_star' : ($i === 2 ? 'two_star' : 'one_star')))];
                        $percentage = ($count / $total) * 100;
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2" style="min-width: 60px;"><?= $i ?> stars</span>
                        <div class="progress flex-fill" style="height: 8px;">
                            <div class="progress-bar bg-warning" 
                                 style="width: <?= $percentage ?>%"></div>
                        </div>
                        <span class="ms-2 text-muted" style="min-width: 50px;"><?= $count ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Individual Reviews -->
    <?php if ($reviews_result->num_rows > 0): ?>
        <?php while ($review = $reviews_result->fetch_assoc()): ?>
        <div class="review-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                    <div class="rating-stars mt-1">
                        <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <small class="text-muted">
                    <?= format_date($review['created_at'], 'M j, Y') ?>
                </small>
            </div>
            
            <?php if (!empty($review['title'])): ?>
                <h6 class="mb-2"><?= htmlspecialchars($review['title']) ?></h6>
            <?php endif; ?>
            
            <p class="mb-1"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
            
            <?php if (!empty($review['response'])): ?>
            <div class="alert alert-light mt-3 mb-0">
                <strong>Hotel Response:</strong>
                <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($review['response'])) ?></p>
                <small class="text-muted">
                    Responded on <?= format_date($review['responded_at']) ?>
                </small>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
            <h5>No reviews yet</h5>
            <p class="text-muted">Be the first to review this hotel!</p>
            <?php if (is_logged_in() && $can_review): ?>
                <a href="write-review.php?hotel_id=<?= $hotel_id ?>" class="btn btn-primary">
                    <i class="fas fa-star me-2"></i>Write the First Review
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
            </div>
            
            <!-- Booking Sidebar -->
            <div class="col-lg-4">
                <div class="booking-card">
                    <h3 class="text-primary mb-3">₱<?= number_format($hotel['price_per_night']) ?></h3>
                    <p class="text-muted">per night</p>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="booking.php?hotel_id=<?= $hotel_id ?>" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                    <?php else: ?>
                        <a href="login.php?redirect=booking.php?hotel_id=<?= $hotel_id ?>" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Book
                        </a>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h6>Quick Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-door-open me-2 text-primary"></i>
                            <?= $hotel['available_rooms'] ?> rooms available
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <?= htmlspecialchars($hotel['phone']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <?= htmlspecialchars($hotel['email']) ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <?= htmlspecialchars($hotel['address']) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(url) {
            document.getElementById('mainImage').src = url;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>