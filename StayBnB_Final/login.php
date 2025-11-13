<?php
/**
 * StayBnB - User Login
 * Copy this to: login.php (REPLACE existing)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Get user from database
        $stmt = $conn->prepare("SELECT user_id, fullname, email, password_hash, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check account status
            if ($user['status'] !== 'active') {
                $error = 'Your account has been suspended. Please contact support.';
            }
            // Verify password (SECURE - bcrypt only, NO plaintext fallback)
            else if (password_verify($password, $user['password_hash'])) {
                // Login successful
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['fullname'];
                
                // Log activity
                log_activity($conn, 'user_login', 'users', $user['user_id']);
                
                // Redirect to intended page or dashboard
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                redirect($redirect, 'Welcome back, ' . $user['fullname'] . '!', 'success');
            } else {
                $error = 'Invalid email or password';
                
                // Log failed attempt
                error_log("Failed login attempt for email: {$email}");
            }
        } else {
            $error = 'Invalid email or password';
        }
        
        $stmt->close();
    }
}

$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StayBnB</title>
    
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
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .login-left {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 60px 40px;
        }
        
        .login-right {
            padding: 60px 40px;
        }
        
        .login-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #0a53fe;
            box-shadow: 0 0 0 0.2rem rgba(10, 83, 254, 0.25);
        }
        
        .btn-login {
            background: #0a53fe;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 83, 254, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="login-container row g-0">
                    <!-- Left Side - Branding -->
                    <div class="col-md-5 login-left d-none d-md-block">
                        <div class="login-logo">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h2 class="mb-4">Welcome to StayBnB</h2>
                        <p class="lead">Your gateway to Bataan's best hotels and resorts.</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Book your perfect stay
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Secure payment processing
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Instant booking confirmation
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                24/7 customer support
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Right Side - Login Form -->
                    <div class="col-md-7 login-right">
                        <h3 class="mb-4">Sign In to Your Account</h3>
                        
                        <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                            <?= htmlspecialchars($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php">
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
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                           required 
                                           autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="forgot-password.php" class="text-decoration-none">
                                Forgot your password?
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">Don't have an account?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="index.php" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>