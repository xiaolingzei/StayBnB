<?php
/**
 * StayBnB - Hotel Recommendations API
 * Implements: Recommendation Algorithm (Objective 1b)
 * Returns personalized hotel recommendations based on user behavior
 */

define('STAYBNB_ACCESS', true);
require_once __DIR__ . '/../config/db_connect.php';

header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID'
    ]);
    exit;
}

// Get recommendations using the algorithm
$recommendations = get_user_recommendations($conn, $user_id, 6);

$hotels = [];
while ($hotel = $recommendations->fetch_assoc()) {
    // Determine recommendation reason
    $reasons = [];
    
    if ($hotel['featured']) {
        $reasons[] = 'Featured property';
    }
    
    if ($hotel['avg_rating'] >= 4.5) {
        $reasons[] = 'Highly rated by guests';
    }
    
    if (!empty($hotel['location'])) {
        // Check if user has booked in this location before
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM bookings b 
            JOIN hotels h ON b.hotel_id = h.hotel_id 
            WHERE b.user_id = ? AND h.location = ?
        ");
        $stmt->bind_param("is", $user_id, $hotel['location']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $reasons[] = 'You liked staying in ' . $hotel['location'];
        }
    }
    
    $hotels[] = [
        'hotel_id' => $hotel['hotel_id'],
        'name' => $hotel['name'],
        'location' => $hotel['location'],
        'description' => substr($hotel['description'], 0, 150) . '...',
        'price_per_night' => $hotel['price_per_night'],
        'image_url' => $hotel['image_url'],
        'avg_rating' => round($hotel['avg_rating'], 1),
        'reasons' => $reasons
    ];
}

echo json_encode([
    'success' => true,
    'recommendations' => $hotels,
    'algorithm' => 'collaborative_filtering',
    'timestamp' => date('Y-m-d H:i:s')
]);

$conn->close();
?>