<?php
/**
 * StayBnB - Reset Password
 * CREATE NEW FILE: reset-password.php
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

$token = sanitize_input($_GET['token'] ?? '');
$error = '';
$success = false;

// Verify token
if (empty($token)) {
    redirect('forgot-password.php', 'Invalid reset link', 'error');
}

$stmt = $conn->prepare("SELECT user_id, fullname, email FROM users WHERE verification_token = ? AND status = 'active'");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('forgot-password.php', 'Invalid or expired reset link', 'error');
}

$user = $result->fetch_assoc();

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Hash new password
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update password and clear token
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, verification_token = NULL WHERE user_id = ?");
        $stmt->bind_param("si", $password_hash, $user['user_id']);
        
        if ($stmt->execute()) {
            log_activity($conn, 'password_reset', 'users', $user['user_id']);
            redirect('login.php', 'Password reset successful! Please login with your new password.', 'success');
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - StayBnB</title>
    
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
        
        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .btn-reset {
            background: #0a53fe;
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            background: #e0e0e0;
        }
        
        .password-strength.weak { background: #dc3545; }
        .password-strength.medium { background: #ffc107; }
        .password-strength.strong { background: #28a745; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="text-center mb-4">
            <i class="fas fa-lock fa-3x text-primary"></i>
            <h3 class="mt-3">Reset Your Password</h3>
            <p class="text-muted">Enter a new password for <?= htmlspecialchars($user['email']) ?></p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="reset-password.php?token=<?= htmlspecialchars($token) ?>" id="resetForm">
            <div class="mb-3">
                <label for="password" class="form-label">New Password *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="At least 6 characters"
                           required
                           onkeyup="checkPasswordStrength()">
                </div>
                <div id="passwordStrength" class="password-strength"></div>
                <small class="text-muted">Use at least 6 characters</small>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Re-enter your password"
                           required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-reset w-100">
                <i class="fas fa-check me-2"></i>Reset Password
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="login.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength >= 3) strengthBar.classList.add('strong');
            else if (strength >= 2) strengthBar.classList.add('medium');
            else if (password.length > 0) strengthBar.classList.add('weak');
        }
        
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>