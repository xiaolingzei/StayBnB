<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $hotel_id = intval($_GET['delete']);
    $conn->query("DELETE FROM hotels WHERE hotel_id = $hotel_id");
    redirect('hotels.php', 'Hotel deleted successfully', 'success');
}

// Get all hotels
$hotels = $conn->query("
    SELECT h.*, hi.image_url 
    FROM hotels h
    LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
    ORDER BY h.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - Admin</title>
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
            <li><a href="hotels.php" class="active"><i class="fas fa-building me-2"></i>Hotels</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar me-2"></i>Bookings</a></li>
            <li><a href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Manage Hotels</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                <i class="fas fa-plus me-2"></i>Add Hotel
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
                        <th>Price</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hotel = $hotels->fetch_assoc()): ?>
                    <tr>
                        <td><?= $hotel['hotel_id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($hotel['image_url']) ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td><?= htmlspecialchars($hotel['name']) ?></td>
                        <td><?= htmlspecialchars($hotel['location']) ?></td>
                        <td>₱<?= number_format($hotel['price_per_night']) ?></td>
                        <td><?= $hotel['star_rating'] ?> ⭐</td>
                        <td>
                            <span class="badge bg-<?= $hotel['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($hotel['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="../hotel-details.php?id=<?= $hotel['hotel_id'] ?>" 
                               class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="hotels.php?delete=<?= $hotel['hotel_id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this hotel?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="php/add_hotel.php">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hotel Name *</label>
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
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price per Night *</label>
                                <input type="number" name="price_per_night" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Star Rating</label>
                                <select name="star_rating" class="form-select">
                                    <option value="3">3 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="5">5 Stars</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Available Rooms</label>
                                <input type="number" name="available_rooms" class="form-control" value="10">
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
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>