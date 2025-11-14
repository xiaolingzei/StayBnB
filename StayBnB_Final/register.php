<?php
/**
 * StayBnB - User Registration
 * Copy this to: register.php (REPLACE existing)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize_input($_POST['fullname'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($fullname)) {
        $errors[] = 'Full name is required';
    } elseif (strlen($fullname) < 3) {
        $errors[] = 'Full name must be at least 3 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Email address is already registered';
        }
        $stmt->close();
    }
    
    // Register user if no errors
    if (empty($errors)) {
        // Hash password with bcrypt (cost 12)
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Generate verification token
        $verification_token = generate_token(32);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, password_hash, verification_token, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("sssss", $fullname, $email, $phone, $password_hash, $verification_token);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Log activity
            $_SESSION['user_id'] = $user_id; // Temp for logging
            log_activity($conn, 'user_registered', 'users', $user_id);
            unset($_SESSION['user_id']); // Clear temp
            
            // Send welcome email (optional)
            // send_email($email, 'Welcome to StayBnB', 'Your account has been created...');
            
            $success = true;
            
            // Auto-login after registration
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $fullname;
            
            redirect('dashboard.php', 'Registration successful! Welcome to StayBnB.', 'success');
        } else {
            $errors[] = 'Registration failed. Please try again.';
            error_log("Registration error: " . $conn->error);
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
    <title>Register - StayBnB</title>
    
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
            padding: 20px 0;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        
        .register-left {
            background: linear-gradient(135deg, #0a53fe 0%, #1e40af 100%);
            color: white;
            padding: 60px 40px;
        }
        
        .register-right {
            padding: 60px 40px;
        }
        
        .register-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #0a53fe;
            box-shadow: 0 0 0 0.2rem rgba(10, 83, 254, 0.25);
        }
        
        .btn-register {
            background: #0a53fe;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 83, 254, 0.4);
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="register-container row g-0">
                    <!-- Left Side - Branding -->
                    <div class="col-md-5 register-left d-none d-md-block">
                        <div class="register-logo">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h2 class="mb-4">Join StayBnB Today</h2>
                        <p class="lead">Create your account and start booking amazing hotels in Bataan.</p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Access exclusive deals
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Save your favorite hotels
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Quick & easy booking
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                Personalized recommendations
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Right Side - Registration Form -->
                    <div class="col-md-7 register-right">
                        <h3 class="mb-4">Create Your Account</h3>
                        
                        <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                            <?= htmlspecialchars($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="register.php" id="registerForm">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="fullname" 
                                           name="fullname" 
                                           placeholder="Juan Dela Cruz"
                                           value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                                           required 
                                           autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
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
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           placeholder="09171234567"
                                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
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
                                <small class="text-muted">Use at least 6 characters with a mix of letters and numbers</small>
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
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-register w-100">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted mb-2">Already have an account?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
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
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrength');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (strength >= 4) strengthBar.classList.add('strong');
            else if (strength >= 2) strengthBar.classList.add('medium');
            else if (password.length > 0) strengthBar.classList.add('weak');
        }
        
        // Validate password match on submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                document.getElementById('confirm_password').focus();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>