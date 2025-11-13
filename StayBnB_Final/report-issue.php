<?php
define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

require_login();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize_input($_POST['subject'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    
    if (empty($subject) || empty($category) || empty($description)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("INSERT INTO user_reports (user_id, subject, category, description, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isss", $_SESSION['user_id'], $subject, $category, $description);
        
        if ($stmt->execute()) {
            $success = true;
            log_activity($conn, 'report_submitted', 'user_reports', $conn->insert_id);
        } else {
            $error = 'Failed to submit report';
        }
    }
}

$user = get_user_data($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report an Issue - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        
        .report-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="report-issue.php">Report Issue</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="report-header">
        <div class="container">
            <h1><i class="fas fa-exclamation-circle me-2"></i>Report an Issue</h1>
            <p class="mb-0">We're here to help. Let us know what's wrong.</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Report Submitted!</strong> We'll review your issue and get back to you soon.
                </div>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <?php else: ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <div class="report-card">
                    <h4 class="mb-4">Describe Your Issue</h4>
                    
                    <form method="POST" action="report-issue.php">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Your Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Booking Issue">Booking Issue</option>
                                <option value="Payment Problem">Payment Problem</option>
                                <option value="Hotel Issue">Hotel Issue</option>
                                <option value="Account Problem">Account Problem</option>
                                <option value="Website Bug">Website Bug</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief summary of your issue" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="6" placeholder="Please provide detailed information about your issue..." required></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Response Time:</strong> We typically respond within 24-48 hours.
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Report
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg ms-2">
                            Cancel
                        </a>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>