<?php
/**
 * StayBnB - Advanced Search Page
 * Implements: Real-time search & filter algorithms (Objective 1a)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Get search filters
$filters = [
    'location' => sanitize_input($_GET['location'] ?? ''),
    'checkin' => sanitize_input($_GET['checkin'] ?? ''),
    'checkout' => sanitize_input($_GET['checkout'] ?? ''),
    'min_price' => floatval($_GET['min_price'] ?? 0),
    'max_price' => floatval($_GET['max_price'] ?? 10000),
    'min_rating' => floatval($_GET['min_rating'] ?? 0),
    'sort_by' => sanitize_input($_GET['sort_by'] ?? 'rating')
];

// Execute search with filters
$hotels = search_hotels($conn, $filters);

// Get all locations for filter
$locations_result = $conn->query("SELECT DISTINCT location FROM hotels WHERE status = 'active' ORDER BY location");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hotels - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .search-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .filter-sidebar {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
        }
        
        .hotel-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .price-badge {
            background: #0a53fe;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .rating-stars {
            color: #fbbf24;
        }
        
        .filter-group {
            margin-bottom: 20px;
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
                    <li class="nav-item"><a class="nav-link active" href="search.php">Search</a></li>
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

    <!-- Search Header -->
    <div class="search-header">
        <div class="container">
            <h1><i class="fas fa-search me-2"></i>Find Your Perfect Stay</h1>
            <p class="mb-0">Discover the best hotels in Bataan</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5 class="mb-4">Filter Results</h5>
                    
                    <form method="GET" action="search.php" id="filterForm">
                        <!-- Location Filter -->
                        <div class="filter-group">
                            <label class="form-label fw-bold">Location</label>
                            <select name="location" class="form-select" onchange="this.form.submit()">
                                <option value="">All Locations</option>
                                <?php while ($loc = $locations_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($loc['location']) ?>" 
                                            <?= $filters['location'] === $loc['location'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loc['location']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="filter-group">
                            <label class="form-label fw-bold">Check-in</label>
                            <input type="date" name="checkin" class="form-control" 
                                   value="<?= htmlspecialchars($filters['checkin']) ?>"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="form-label fw-bold">Check-out</label>
                            <input type="date" name="checkout" class="form-control" 
                                   value="<?= htmlspecialchars($filters['checkout']) ?>"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-group">
                            <label class="form-label fw-bold">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" 
                                           placeholder="Min" value="<?= $filters['min_price'] ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" 
                                           placeholder="Max" value="<?= $filters['max_price'] ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rating Filter -->
                        <div class="filter-group">
                            <label class="form-label fw-bold">Minimum Rating</label>
                            <select name="min_rating" class="form-select">
                                <option value="0" <?= $filters['min_rating'] == 0 ? 'selected' : '' ?>>Any Rating</option>
                                <option value="3" <?= $filters['min_rating'] == 3 ? 'selected' : '' ?>>3+ Stars</option>
                                <option value="4" <?= $filters['min_rating'] == 4 ? 'selected' : '' ?>>4+ Stars</option>
                                <option value="4.5" <?= $filters['min_rating'] == 4.5 ? 'selected' : '' ?>>4.5+ Stars</option>
                            </select>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="filter-group">
                            <label class="form-label fw-bold">Sort By</label>
                            <select name="sort_by" class="form-select" onchange="this.form.submit()">
                                <option value="rating" <?= $filters['sort_by'] === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                                <option value="price_asc" <?= $filters['sort_by'] === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= $filters['sort_by'] === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                        <a href="search.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-2"></i>Clear All
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Results -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <?php 
                        $total = $hotels->num_rows;
                        echo $total . ' hotel' . ($total != 1 ? 's' : '') . ' found';
                        ?>
                    </h4>
                </div>
                
                <?php if ($hotels->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($hotel = $hotels->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card hotel-card h-100">
                                <img src="<?= htmlspecialchars($hotel['image_url']) ?>" 
                                     class="hotel-image" 
                                     alt="<?= htmlspecialchars($hotel['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($hotel['name']) ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($hotel['location']) ?>
                                    </p>
                                    <div class="rating-stars mb-2">
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
                                        <span class="text-muted ms-1">(<?= number_format($rating, 1) ?>)</span>
                                    </div>
                                    <p class="card-text text-muted small">
                                        <?= substr($hotel['description'], 0, 100) ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <span class="price-badge">₱<?= number_format($hotel['price_per_night']) ?></span>
                                            <small class="text-muted d-block">/night</small>
                                        </div>
                                        <a href="hotel-details.php?id=<?= $hotel['hotel_id'] ?>" 
                                           class="btn btn-primary btn-sm">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4>No hotels found</h4>
                        <p class="text-muted">Try adjusting your filters</p>
                        <a href="search.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>