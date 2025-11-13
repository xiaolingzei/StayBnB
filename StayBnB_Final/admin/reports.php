<?php
define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if (isset($_POST['update_report'])) {
    $report_id = intval($_POST['report_id']);
    $status = sanitize_input($_POST['status']);
    $response = sanitize_input($_POST['admin_response']);
    
    $conn->query("UPDATE user_reports SET status = '$status', admin_response = '$response' WHERE report_id = $report_id");
    redirect('reports.php', 'Report updated', 'success');
}

$reports = $conn->query("
    SELECT r.*, u.fullname, u.email 
    FROM user_reports r
    JOIN users u ON r.user_id = u.user_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reports - Admin</title>
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
            <li><a href="reviews.php"><i class="fas fa-star me-2"></i>Reviews</a></li>
            <li><a href="reports.php" class="active"><i class="fas fa-exclamation-triangle me-2"></i>Reports</a></li>
            <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h2>User Reports</h2>
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
                        <th>User</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $reports->fetch_assoc()): ?>
                    <tr>
                        <td><?= $report['report_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($report['fullname']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($report['email']) ?></small>
                        </td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($report['category']) ?></span></td>
                        <td><?= htmlspecialchars($report['subject']) ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $report['status'] === 'resolved' ? 'success' : 
                                ($report['status'] === 'pending' ? 'warning' : 'secondary') 
                            ?>">
                                <?= ucfirst($report['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($report['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#reportModal<?= $report['report_id'] ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>

                    <!-- Modal -->
                    <div class="modal fade" id="reportModal<?= $report['report_id'] ?>">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Report Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                        
                                        <p><strong>Description:</strong></p>
                                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" <?= $report['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="in_progress" <?= $report['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                                <option value="resolved" <?= $report['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                                <option value="closed" <?= $report['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Admin Response</label>
                                            <textarea name="admin_response" class="form-control" rows="3"><?= htmlspecialchars($report['admin_response'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="update_report" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>