<?php
/**
 * StayBnB - Database Connection & Core Functions
 * COMPLETE VERSION - All functions included
 */

if (!defined('STAYBNB_ACCESS')) {
    define('STAYBNB_ACCESS', true);
}

// ========== DATABASE CONFIGURATION ==========
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'staybnb_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========== SITE SETTINGS ==========
define('SITE_NAME', 'StayBnB');
define('SITE_URL', 'http://localhost/StayBnB_Final/');
define('ADMIN_EMAIL', 'admin@staybnb.com');
define('ITEMS_PER_PAGE', 12);

// ========== ERROR REPORTING ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========== DATABASE CONNECTION ==========
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset(DB_CHARSET);
    $conn->query("SET time_zone = '+08:00'");
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// ========== SESSION CONFIGURATION ==========
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

date_default_timezone_set('Asia/Manila');

// ========== CORE FUNCTIONS ==========

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_login($redirect = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = 'Please login to continue';
        $_SESSION['flash_type'] = 'warning';
        header("Location: " . $redirect);
        exit();
    }
}

function require_admin($redirect = 'admin/login.php') {
    if (!is_admin()) {
        header("Location: " . $redirect);
        exit();
    }
}

function redirect($url, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: " . $url);
    exit();
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $msg;
    }
    return null;
}

function format_currency($amount) {
    return '₱' . number_format($amount, 2);
}

function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'F j, Y g:i A') {
    return date($format, strtotime($datetime));
}

function generate_booking_ref() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

function get_logged_user($conn) {
    if (!is_logged_in()) return null;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function calculate_nights($checkin, $checkout) {
    $start = new DateTime($checkin);
    $end = new DateTime($checkout);
    return $start->diff($end)->days;
}

function log_activity($conn, $action, $entity_type = null, $entity_id = null) {
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $admin_id = is_admin() ? $_SESSION['admin_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, admin_id, action, entity_type, entity_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iississ", $user_id, $admin_id, $action, $entity_type, $entity_id, $ip, $agent);
    $stmt->execute();
}

// ========== SEARCH & FILTER ALGORITHMS (Objective 1a) ==========

function search_hotels($conn, $filters = []) {
    $location = $filters['location'] ?? '';
    $checkin = $filters['checkin'] ?? '';
    $checkout = $filters['checkout'] ?? '';
    $min_price = $filters['min_price'] ?? 0;
    $max_price = $filters['max_price'] ?? 999999;
    $min_rating = $filters['min_rating'] ?? 0;
    $sort_by = $filters['sort_by'] ?? 'rating';
    
    $sql = "SELECT h.*, hi.image_url, 
            COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
            COUNT(DISTINCT r.review_id) as review_count
            FROM hotels h
            LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
            LEFT JOIN reviews r ON h.hotel_id = r.hotel_id AND r.status = 'approved'
            WHERE h.status = 'active'";
    
    $params = [];
    $types = '';
    
    if (!empty($location)) {
        $sql .= " AND h.location LIKE ?";
        $params[] = "%{$location}%";
        $types .= 's';
    }
    
    if ($min_price > 0) {
        $sql .= " AND h.price_per_night >= ?";
        $params[] = $min_price;
        $types .= 'd';
    }
    
    if ($max_price < 999999) {
        $sql .= " AND h.price_per_night <= ?";
        $params[] = $max_price;
        $types .= 'd';
    }
    
    $sql .= " GROUP BY h.hotel_id";
    
    if ($min_rating > 0) {
        $sql .= " HAVING avg_rating >= ?";
        $params[] = $min_rating;
        $types .= 'd';
    }
    
    // Sorting algorithm
    switch ($sort_by) {
        case 'price_asc':
            $sql .= " ORDER BY h.price_per_night ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY h.price_per_night DESC";
            break;
        case 'rating':
        default:
            $sql .= " ORDER BY avg_rating DESC, review_count DESC";
            break;
    }
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// ========== RECOMMENDATION ALGORITHM (Objective 1b) ==========

function get_user_recommendations($conn, $user_id, $limit = 6) {
    // Algorithm: Collaborative filtering based on user preferences
    
    // Get user's booking history
    $stmt = $conn->prepare("
        SELECT h.location, AVG(h.price_per_night) as avg_price
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.hotel_id
        WHERE b.user_id = ? AND b.status != 'cancelled'
        GROUP BY h.location
        ORDER BY COUNT(*) DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pref = $stmt->get_result()->fetch_assoc();
    
    if ($pref) {
        // Recommend similar hotels
        $location = $pref['location'];
        $price = $pref['avg_price'];
        
        $stmt = $conn->prepare("
            SELECT h.*, hi.image_url,
            COALESCE(AVG(r.rating), h.star_rating) as avg_rating,
            (
                CASE WHEN h.location = ? THEN 3 ELSE 0 END +
                CASE WHEN ABS(h.price_per_night - ?) < 1000 THEN 2 ELSE 0 END +
                CASE WHEN h.featured = 1 THEN 1 ELSE 0 END
            ) as relevance_score
            FROM hotels h
            LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
            LEFT JOIN reviews r ON h.hotel_id = r.hotel_id
            WHERE h.status = 'active'
            AND h.hotel_id NOT IN (
                SELECT hotel_id FROM bookings WHERE user_id = ?
            )
            GROUP BY h.hotel_id
            ORDER BY relevance_score DESC, avg_rating DESC
            LIMIT ?
        ");
        $stmt->bind_param("sdii", $location, $price, $user_id, $limit);
    } else {
        // New user: recommend featured high-rated hotels
        $stmt = $conn->prepare("
            SELECT h.*, hi.image_url,
            COALESCE(AVG(r.rating), h.star_rating) as avg_rating
            FROM hotels h
            LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
            LEFT JOIN reviews r ON h.hotel_id = r.hotel_id
            WHERE h.status = 'active'
            GROUP BY h.hotel_id
            ORDER BY h.featured DESC, avg_rating DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

?>