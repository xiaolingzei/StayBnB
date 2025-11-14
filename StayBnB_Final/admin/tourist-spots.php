<?php
/**
 * StayBnB - Admin Tourist Spots Management
 * CREATE NEW FILE: admin/tourist-spots.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $spot_id = intval($_GET['delete']);
    $conn->query("DELETE FROM tourist_spots WHERE spot_id = $spot_id");
    redirect('tourist-spots.php', 'Tourist spot deleted successfully', 'success');
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_spot'])) {
    $name = sanitize_input($_POST['name']);
    $location = sanitize_input($_POST['location']);
    $description = sanitize_input($_POST['description']);
    $category = sanitize_input($_POST['category']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $image_url = sanitize_input($_POST['image_url']);
    
    $stmt = $conn->prepare("INSERT INTO tourist_spots (name, location, description, category, latitude, longitude, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdds", $name, $location, $description, $category, $latitude, $longitude, $image_url);
    
    if ($stmt->execute()) {
        redirect('tourist-spots.php', 'Tourist spot added successfully!', 'success');
    } else {
        redirect('tourist-spots.php', 'Error adding tourist spot', 'error');
    }
}

// Get all tourist spots
$spots = $conn->query("SELECT * FROM tourist_spots ORDER BY location, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tourist Spots - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-title">
            <h4><i class="fas fa-hotel"></i> StayBnB</h4>
            <p class="mb-0 small">Admin Panel</p>
        </div>
        <ul class="sidebar-nav">
            <li><a href="index.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
            <li><a href="hotels.php"><i class="fas fa-building me-2"></i>Hotels</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar me-2"></i>Bookings</a></li>
            <li><a href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
            <li><a href="reviews.php"><i class="fas fa-star me-2"></i>Reviews</a></li>
            <li><a href="reports.php"><i class="fas fa-exclamation-triangle me-2"></i>Reports</a></li>
            <li><a href="tourist-spots.php" class="active"><i class="fas fa-map-marked-alt me-2"></i>Tourist Spots</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Manage Tourist Spots</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSpotModal">
                <i class="fas fa-plus me-2"></i>Add Tourist Spot
            </button>
        </div>

        <?php if ($flash = get_flash_message()): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Coordinates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($spot = $spots->fetch_assoc()): ?>
                    <tr>
                        <td><?= $spot['spot_id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($spot['image_url']) ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td><?= htmlspecialchars($spot['name']) ?></td>
                        <td><?= htmlspecialchars($spot['location']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($spot['category']) ?></span></td>
                        <td><?= $spot['latitude'] ?>, <?= $spot['longitude'] ?></td>
                        <td>
                            <a href="tourist-spots.php?delete=<?= $spot['spot_id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this spot?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Spot Modal -->
    <div class="modal fade" id="addSpotModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tourist Spot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="tourist-spots.php">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Spot Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location *</label>
                                <select name="location" class="form-select" required>
                                    <option value="Balanga City">Balanga City</option>
                                    <option value="Morong">Morong</option>
                                    <option value="Bagac">Bagac</option>
                                    <option value="Mariveles">Mariveles</option>
                                    <option value="Limay">Limay</option>
                                    <option value="Pilar">Pilar</option>
                                    <option value="Hermosa">Hermosa</option>
                                    <option value="Dinalupihan">Dinalupihan</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="Historical">Historical Site</option>
                                    <option value="Beach">Beach</option>
                                    <option value="Mountain">Mountain</option>
                                    <option value="Park">Park</option>
                                    <option value="Museum">Museum</option>
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Shopping">Shopping</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Latitude *</label>
                                <input type="number" step="0.000001" name="latitude" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Longitude *</label>
                                <input type="number" step="0.000001" name="longitude" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Image URL</label>
                                <input type="url" name="image_url" class="form-control" 
                                       placeholder="https://images.unsplash.com/...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_spot" class="btn btn-primary">Add Spot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>