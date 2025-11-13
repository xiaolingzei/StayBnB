<?php
/**
 * StayBnB - Database Connection & Security Configuration
 * Copy this to: config/db_connect.php
 */

// Security check
if (!defined('STAYBNB_ACCESS')) {
    define('STAYBNB_ACCESS', true);
}

// ========== DATABASE CONFIGURATION ==========
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'staybnb_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change this if you have a password
define('DB_CHARSET', 'utf8mb4');

// ========== ENVIRONMENT SETTINGS ==========
define('ENVIRONMENT', 'development'); // Change to 'production' when deploying
define('DEBUG_MODE', ENVIRONMENT === 'development');

// ========== SITE SETTINGS ==========
define('SITE_NAME', 'StayBnB');
define('SITE_URL', 'http://localhost/StayBnB_Final/');
define('ADMIN_EMAIL', 'admin@staybnb.com');
define('ITEMS_PER_PAGE', 12);

// ========== FILE UPLOAD SETTINGS ==========
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// ========== ERROR REPORTING ==========
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}

// ========== DATABASE CONNECTION ==========
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if (!$conn->set_charset(DB_CHARSET)) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
    $conn->query("SET time_zone = '+08:00'"); // Philippine Time
    
} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    
    if (DEBUG_MODE) {
        die("Database Connection Error: " . $e->getMessage());
    } else {
        die("Sorry, we're experiencing technical difficulties. Please try again later.");
    }
}

// ========== SECURE SESSION CONFIGURATION ==========
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    
    session_start();
    
    // Session regeneration for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// ========== TIMEZONE ==========
date_default_timezone_set('Asia/Manila');

// ========== HELPER FUNCTIONS ==========

/**
 * Sanitize input to prevent XSS
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate secure random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function is_admin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require login - redirect if not authenticated
 */
function require_login($redirect_to = 'login.php') {
    if (!is_logged_in()) {
        redirect($redirect_to, 'Please login to continue', 'warning');
    }
}

/**
 * Require admin - redirect if not admin
 */
function require_admin($redirect_to = 'admin/login.php') {
    if (!is_admin()) {
        redirect($redirect_to, 'Admin access required', 'error');
    }
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    // Handle relative URLs
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = SITE_URL . $url;
    }
    
    header("Location: " . $url);
    exit();
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Format currency (Philippine Peso)
 */
function format_currency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date
 */
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format date with time
 */
function format_datetime($datetime, $format = 'F j, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Generate booking reference
 */
function generate_booking_ref() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Safe database query with prepared statement
 */
function db_query($conn, $query, $types = '', $params = []) {
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

/**
 * Get current user data
 */
function get_current_user($conn) {
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Send email notification
 */
function send_email($to, $subject, $body, $from_name = null) {
    $from_name = $from_name ?? SITE_NAME;
    $from_email = ADMIN_EMAIL;
    
    $headers = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "Reply-To: {$from_email}\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // In production, use SMTP instead of mail()
    if (ENVIRONMENT === 'production') {
        // TODO: Implement PHPMailer or similar
        // For now, just log
        error_log("Email to {$to}: {$subject}");
        return true;
    }
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Log activity
 */
function log_activity($conn, $action, $entity_type = null, $entity_id = null) {
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $admin_id = is_admin() ? $_SESSION['admin_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, admin_id, action, entity_type, entity_id, ip_address, user_agent) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iississ", $user_id, $admin_id, $action, $entity_type, $entity_id, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

/**
 * Calculate nights between dates
 */
function calculate_nights($checkin, $checkout) {
    $start = new DateTime($checkin);
    $end = new DateTime($checkout);
    $diff = $start->diff($end);
    return $diff->days;
}

/**
 * Upload image file
 */
function upload_image($file, $destination_folder = 'hotels') {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_PATH . $destination_folder . '/' . $filename;
    
    // Create directory if doesn't exist
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true, 
            'filename' => $filename,
            'path' => 'uploads/' . $destination_folder . '/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

// ========== INITIALIZATION COMPLETE ==========
// Database connection $conn is now available globally
// All helper functions are loaded
?>