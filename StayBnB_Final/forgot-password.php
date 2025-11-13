<?php
/**
 * StayBnB - Forgot Password
 * CREATE NEW FILE: forgot-password.php
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id, fullname FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $conn->prepare("UPDATE users SET verification_token = ?, updated_at = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $reset_token, $reset_expires, $user['user_id']);
            $stmt->execute();
            
            // Create reset link
            $reset_link = SITE_URL . "reset-password.php?token=" . $reset_token;
            
            // In production, send email here
            // send_email($email, 'Password Reset', "Click here to reset: $reset_link");
            
            $success = true;
            
            // For development - show link
            $_SESSION['reset_link'] = $reset_link;
        } else {
            // For security, don't reveal if email exists
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .icon-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .icon-wrapper i {
            font-size: 4rem;
            color: #0a53fe;
        }
        
        .btn-reset {
            background: #0a53fe;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        
        .btn-reset:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="icon-wrapper">
            <i class="fas fa-key"></i>
            <h3 class="mt-3">Forgot Password?</h3>
            <p class="text-muted">Enter your email to reset your password</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Check your email!</strong>
                <p class="mb-0 mt-2">If an account exists with that email, we've sent password reset instructions.</p>
                
                <?php if (isset($_SESSION['reset_link'])): ?>
                <div class="alert alert-warning mt-3">
                    <strong>Development Mode:</strong>
                    <p class="mb-1 small">Email not configured. Use this link:</p>
                    <a href="<?= $_SESSION['reset_link'] ?>" class="small"><?= $_SESSION['reset_link'] ?></a>
                </div>
                <?php unset($_SESSION['reset_link']); endif; ?>
            </div>
            
            <a href="login.php" class="btn btn-outline-primary w-100">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
            
        <?php else: ?>
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="forgot-password.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="your.email@example.com"
                               required 
                               autofocus>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-reset w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>
                
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>