<?php
/**
 * StayBnB - Admin Login
 * REPLACE: admin/login.php
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Check password - allow both hashed and plain text for default admin
$password_valid = false;

if (password_verify($password, $admin['password_hash'])) {
    $password_valid = true;
} elseif ($username === 'admin' && $password === 'admin123') {
    // Default admin with plain password - update hash
    $new_hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    $conn->query("UPDATE admins SET password_hash = '$new_hash' WHERE admin_id = " . $admin['admin_id']);
    $password_valid = true;
}

if ($password_valid) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Update last login
                $conn->query("UPDATE admins SET last_login = NOW() WHERE admin_id = " . $admin['admin_id']);
                
                log_activity($conn, 'admin_login', 'admins', $admin['admin_id']);
                
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StayBnB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="text-center mb-4">
            <i class="fas fa-shield-alt fa-3x text-primary"></i>
            <h3 class="login-title mt-3">Admin Login</h3>
            <p class="text-muted">StayBnB Administration Panel</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="input-group mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" 
                       placeholder="Enter username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required autofocus>
            </div>
            
            <div class="input-group mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" 
                       placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
        
        <div class="login-note mt-3">
            <a href="../index.php" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to Main Site
            </a>
        </div>
        
        <div class="alert alert-info mt-3 small">
            <strong>Default Login:</strong><br>
            Username: admin<br>
            Password: admin123
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>