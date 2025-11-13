<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = sanitize_input($_POST['status']);
    $conn->query("UPDATE bookings SET status = '$status' WHERE booking_id = $booking_id");
    redirect('bookings.php', 'Booking status updated', 'success');
}

// Get filter
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : 'all';

$sql = "SELECT b.*, h.name as hotel_name, u.fullname, u.email 
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.hotel_id
        JOIN users u ON b.user_id = u.user_id";

if ($filter !== 'all') {
    $sql .= " WHERE b.status = '$filter'";
}

$sql .= " ORDER BY b.created_at DESC";

$bookings = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
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
            <li><a href="bookings.php" class="active"><i class="fas fa-calendar me-2"></i>Bookings</a></li>
            <li><a href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Manage Bookings</h2>
        </div>

        <?php if ($flash = get_flash_message()): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Buttons -->
        <div class="mb-3">
            <a href="bookings.php?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
            <a href="bookings.php?filter=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
            <a href="bookings.php?filter=confirmed" class="btn btn-sm <?= $filter === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?>">Confirmed</a>
            <a href="bookings.php?filter=cancelled" class="btn btn-sm <?= $filter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled</a>
        </div>

        <div class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Guest</th>
                        <th>Hotel</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings->num_rows > 0): ?>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['booking_ref']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($booking['fullname']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($booking['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($booking['hotel_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($booking['checkin_date'])) ?></td>
                            <td><?= date('M j, Y', strtotime($booking['checkout_date'])) ?></td>
                            <td>₱<?= number_format($booking['total_amount']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $booking['status'] === 'confirmed' ? 'success' : 
                                    ($booking['status'] === 'pending' ? 'warning' : 'secondary') 
                                ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal<?= $booking['booking_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>

                                <!-- Status Update Modal -->
                                <div class="modal fade" id="statusModal<?= $booking['booking_id'] ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Booking Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="checked_in" <?= $booking['status'] === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                                                        <option value="checked_out" <?= $booking['status'] === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                                                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>