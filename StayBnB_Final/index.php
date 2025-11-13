<?php
/**
 * StayBnB - Main Homepage
 * Copy this to: index.php (root folder, REPLACE index.html)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Get featured hotels (Bataan only)
$featured_query = "SELECT h.*, hi.image_url, 
                   COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
                   COUNT(DISTINCT r.review_id) as review_count
                   FROM hotels h
                   LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
                   LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.status = 'approved'
                   WHERE h.status = 'active' AND h.featured = 1
                   GROUP BY h.hotel_id
                   ORDER BY h.star_rating DESC
                   LIMIT 6";

$featured_result = $conn->query($featured_query);

// Get all active hotels
$hotels_query = "SELECT h.*, hi.image_url, 
                 COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
                 COUNT(DISTINCT r.review_id) as review_count
                 FROM hotels h
                 LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
                 LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.status = 'approved'
                 WHERE h.status = 'active'
                 GROUP BY h.hotel_id
                 ORDER BY h.featured DESC, avg_rating DESC
                 LIMIT 12";

$hotels_result = $conn->query($hotels_query);

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book the best hotels and resorts in Bataan, Philippines. Experience history, beaches, and world-class hospitality.">
    <title>StayBnB - Discover Bataan's Best Hotels & Resorts</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="search.php">Search Hotels</a></li>
<li class="nav-item"><a class="nav-link" href="ai-assistant.php">🤖 AI Assistant</a></li> <!-- ADD THIS LINE -->

    <style>
        :root {
            --primary: #0a53fe;
            --secondary: #1e40af;
            --accent: #fbbf24;
        }
        
        @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

        body { font-family: 'Segoe UI', sans-serif; }
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                        url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?w=2000');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 150px 0 100px;
        }
        
        .hero-title { font-size: 3.5rem; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        
        .search-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-top: 2rem;
        }
        
        .hotel-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .hotel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .hotel-image { height: 220px; object-fit: cover; width: 100%; }
        .hotel-price { color: var(--primary); font-size: 1.5rem; font-weight: 700; }
        .rating-stars { color: var(--accent); }
        
        .badge-featured {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: #1f2937;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .highlight-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .highlight-card:hover { transform: translateY(-5px); }
        .highlight-icon { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hotel"></i> StayBnB
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="search.php">Search Hotels</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">My Bookings</a></li>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2 px-3" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Message -->
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show mt-5 pt-5" role="alert">
        <div class="container">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Discover Bataan's Hidden Gems</h1>
            <p class="lead mb-4">Experience history, beaches, and world-class hospitality</p>
            
            <div class="search-card">
                <form action="search.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-dark fw-semibold">Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations in Bataan</option>
                            <option value="Morong">Morong</option>
                            <option value="Bagac">Bagac</option>
                            <option value="Balanga City">Balanga City</option>
                            <option value="Mariveles">Mariveles</option>
                            <option value="Limay">Limay</option>
                            <option value="Pilar">Pilar</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-dark fw-semibold">Check-in</label>
                        <input type="date" name="checkin" class="form-control" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-dark fw-semibold">Check-out</label>
                        <input type="date" name="checkout" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-dark fw-semibold">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Featured Hotels -->
    <?php if ($featured_result && $featured_result->num_rows > 0): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Featured Properties</h2>
                <p class="text-muted">Handpicked selections of Bataan's finest accommodations</p>
            </div>
            
            <div class="row g-4">
                <?php while ($hotel = $featured_result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card hotel-card">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($hotel['image_url'] ?? 'assets/images/default-hotel.jpg') ?>" 
                                 class="hotel-image" 
                                 alt="<?= htmlspecialchars($hotel['name']) ?>">
                            <span class="badge-featured">Featured</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($hotel['name']) ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= htmlspecialchars($hotel['location']) ?>
                            </p>
                            <div class="rating-stars mb-2">
                                <?php for ($i = 0; $i < floor($hotel['avg_rating']); $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                                <span class="text-muted ms-1">(<?= number_format($hotel['avg_rating'], 1) ?>)</span>
                            </div>
                            <p class="card-text text-muted small mb-3">
                                <?= substr(htmlspecialchars($hotel['description']), 0, 100) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="hotel-price">₱<?= number_format($hotel['price_per_night']) ?></span>
                                    <small class="text-muted">/night</small>
                                </div>
                                <a href="hotel-details.php?id=<?= $hotel['hotel_id'] ?>" class="btn btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="search.php" class="btn btn-outline-primary btn-lg">
                    View All Hotels <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Why Visit Bataan -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Visit Bataan?</h2>
                <p class="text-muted">Rich history meets natural beauty</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="highlight-card">
                        <div class="highlight-icon"><i class="fas fa-landmark"></i></div>
                        <h5>Historical Sites</h5>
                        <p class="text-muted">Visit Mt. Samat, Death March markers, and war memorials</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="highlight-card">
                        <div class="highlight-icon"><i class="fas fa-umbrella-beach"></i></div>
                        <h5>Beautiful Beaches</h5>
                        <p class="text-muted">Crystal clear waters and pristine coastlines</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="highlight-card">
                        <div class="highlight-icon"><i class="fas fa-utensils"></i></div>
                        <h5>Local Cuisine</h5>
                        <p class="text-muted">Fresh seafood and authentic Filipino dishes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="highlight-card">
                        <div class="highlight-icon"><i class="fas fa-mountain"></i></div>
                        <h5>Nature Trails</h5>
                        <p class="text-muted">Explore mountains, waterfalls, and eco-parks</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- All Hotels -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">All Available Hotels</h2>
                <p class="text-muted">Browse our complete selection</p>
            </div>
            
            <div class="row g-4">
                <?php if ($hotels_result && $hotels_result->num_rows > 0): ?>
                    <?php while ($hotel = $hotels_result->fetch_assoc()): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card hotel-card">
                            <img src="<?= htmlspecialchars($hotel['image_url'] ?? 'assets/images/default-hotel.jpg') ?>" 
                                 class="hotel-image" 
                                 alt="<?= htmlspecialchars($hotel['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($hotel['name']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($hotel['location']) ?>
                                </p>
                                <div class="rating-stars mb-2">
                                    <?php for ($i = 0; $i < floor($hotel['avg_rating']); $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-1">
                                        (<?= number_format($hotel['avg_rating'], 1) ?>) • 
                                        <?= $hotel['review_count'] ?> reviews
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <span class="hotel-price">₱<?= number_format($hotel['price_per_night']) ?></span>
                                        <small class="text-muted">/night</small>
                                    </div>
                                    <a href="hotel-details.php?id=<?= $hotel['hotel_id'] ?>" class="btn btn-primary">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No hotels available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><i class="fas fa-hotel me-2"></i>StayBnB</h5>
                    <p>Your gateway to Bataan's best hotels and resorts. Experience history, nature, and hospitality.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="search.php" class="text-white-50 text-decoration-none">Browse Hotels</a></li>
                        
                        <?php if (is_admin()): ?>
                        <li class="mb-2"><a href="admin/index.php" class="text-white-50 text-decoration-none">Admin Panel</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Contact</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope me-2"></i><?= ADMIN_EMAIL ?><br>
                        <i class="fas fa-phone me-2"></i>+63 917 123 4567<br>
                        <i class="fas fa-map-marker-alt me-2"></i>Balanga City, Bataan
                    </p>
                </div>
            </div>
            <hr class="bg-white opacity-25">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; <?= date('Y') ?> StayBnB. All rights reserved. | Developed by BPSU CS Students</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>