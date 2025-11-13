<?php
/**
 * StayBnB - Enhanced User Profile
 * REPLACE: profile.php
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';
require_login();

$user = get_user_data($conn);
$error = '';
$success = '';

// Get user statistics
$stats_query = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_trips,
                SUM(total_amount) as total_spent,
                MIN(created_at) as member_since
                FROM bookings
                WHERE user_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = sanitize_input($_POST['fullname']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    if (empty($fullname)) {
        $error = 'Full name is required';
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("sssi", $fullname, $phone, $address, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $fullname;
            $success = 'Profile updated successfully!';
            $user = get_user_data($conn); // Refresh data
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password)) {
        $error = 'All password fields are required';
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("si", $new_hash, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password';
        }
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .profile-header {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stat-box h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #0a53fe;
            margin: 0 auto 20px;
            border: 5px solid rgba(255,255,255,0.3);
        }
        
        .section-title {
            border-bottom: 2px solid #0a53fe;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #0a53fe;
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <a href="dashboard.php" class="btn btn-light btn-sm mb-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <h1><i class="fas fa-user-circle me-2"></i>My Profile</h1>
                    <p class="mb-0">Manage your account information and preferences</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-4">
                <div class="profile-card text-center">
                    <div class="avatar-circle">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4><?= htmlspecialchars($user['fullname']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-success mb-3">
                        <i class="fas fa-check-circle me-1"></i>Verified Account
                    </span>
                    <p class="text-muted small">
                        <i class="fas fa-calendar me-2"></i>
                        Member since <?= date('F Y', strtotime($stats['member_since'])) ?>
                    </p>
                </div>

                <!-- Statistics -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="stat-box">
                            <h3><?= $stats['total_bookings'] ?></h3>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <h3><?= $stats['completed_trips'] ?></h3>
                            <p class="mb-0">Completed Trips</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <h3>₱<?= number_format($stats['total_spent']) ?></h3>
                            <p class="mb-0">Total Spent</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="profile-card">
                    <h4 class="section-title">
                        <i class="fas fa-user-edit me-2"></i>Personal Information
                    </h4>
                    
                    <form method="POST" action="profile.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="fullname" class="form-control" 
                                           value="<?= htmlspecialchars($user['fullname']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                           placeholder="09171234567">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Account Status</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                    <input type="text" class="form-control" 
                                           value="<?= ucfirst($user['status']) ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <textarea name="address" class="form-control" rows="2" 
                                              placeholder="Enter your complete address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="profile-card">
                    <h4 class="section-title">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </h4>
                    
                    <form method="POST" action="profile.php">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="current_password" class="form-control" 
                                           placeholder="Enter current password" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="new_password" class="form-control" 
                                           placeholder="At least 6 characters" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" 
                                           placeholder="Re-enter new password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fas fa-shield-alt me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Actions -->
                <div class="profile-card">
                    <h4 class="section-title">
                        <i class="fas fa-cog me-2"></i>Account Actions
                    </h4>
                    
                    <div class="d-grid gap-2">
                        <a href="my-bookings.php" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-alt me-2"></i>View My Bookings
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-info">
                            <i class="fas fa-th-large me-2"></i>Go to Dashboard
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>