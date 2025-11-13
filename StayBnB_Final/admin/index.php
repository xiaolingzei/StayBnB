<?php
/**
 * StayBnB - Admin Dashboard
 * REPLACE: admin/index.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

$result = $conn->query("SELECT COUNT(*) as count FROM hotels WHERE status = 'active'");
$stats['hotels'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM bookings");
$stats['bookings'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$stats['users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT SUM(total_amount) as revenue FROM bookings WHERE status != 'cancelled'");
$stats['revenue'] = $result->fetch_assoc()['revenue'] ?? 0;

// Get recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, h.name as hotel_name, u.fullname 
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.hotel_id
    JOIN users u ON b.user_id = u.user_id
    ORDER BY b.created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StayBnB</title>
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
            <li><a href="index.php" class="active"><i class="fas fa-home me-2"></i>Dashboard</a></li>
            <li><a href="hotels.php"><i class="fas fa-building me-2"></i>Hotels</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar me-2"></i>Bookings</a></li>
            <li><a href="tourist-spots.php"><i class="fas fa-map-marked-alt me-2"></i>Tourist Spots</a></li>
            <li><a href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
            <li><a href="reviews.php"><i class="fas fa-star me-2"></i>Reviews</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Dashboard Overview</h2>
            <div>
                <span class="me-3">Welcome, Admin!</span>
                <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="cards">
            <div class="card">
                <div class="stat-icon blue">
                    <i class="fas fa-building"></i>
                </div>
                <h3><?= $stats['hotels'] ?></h3>
                <p>Total Hotels</p>
            </div>
            <div class="card">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?= $stats['bookings'] ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="card">
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?= $stats['users'] ?></h3>
                <p>Registered Users</p>
            </div>
            <div class="card">
                <div class="stat-icon purple">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <h3>₱<?= number_format($stats['revenue']) ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="table-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Recent Bookings</h4>
                <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Booking Ref</th>
                        <th>Guest</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_bookings->num_rows > 0): ?>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_ref']) ?></td>
                            <td><?= htmlspecialchars($booking['fullname']) ?></td>
                            <td><?= htmlspecialchars($booking['hotel_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($booking['checkin_date'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>₱<?= number_format($booking['total_amount']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No bookings yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>