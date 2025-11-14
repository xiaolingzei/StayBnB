<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$reviews = $conn->query("
    SELECT r.*, u.fullname, h.name as hotel_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN hotels h ON r.hotel_id = h.hotel_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="admin-body">
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
            <li><a href="reviews.php" class="active"><i class="fas fa-star me-2"></i>Reviews</a></li>
            <li><a href="reports.php"><i class="fas fa-exclamation-triangle me-2"></i>Reports</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h2>User Reviews</h2>
        </div>

        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Hotel</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['fullname']) ?></td>
                        <td><?= htmlspecialchars($review['hotel_name']) ?></td>
                        <td>
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php endfor; ?>
                            (<?= $review['rating'] ?>)
                        </td>
                        <td><?= htmlspecialchars($review['comment']) ?></td>
                        <td><?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>