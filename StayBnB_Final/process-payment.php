<?php
/**
 * StayBnB - Process Payment
 * CREATE NEW FILE: process-payment.php
 * Handles payment processing for bookings
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $payment_method = sanitize_input($_POST['payment_method'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    
    // Payment method specific data
    $gcash_number = sanitize_input($_POST['gcash_number'] ?? '');
    $maya_number = sanitize_input($_POST['maya_number'] ?? '');
    $card_number = sanitize_input($_POST['card_number'] ?? '');
    $card_name = sanitize_input($_POST['card_name'] ?? '');
    $card_expiry = sanitize_input($_POST['card_expiry'] ?? '');
    $card_cvv = sanitize_input($_POST['card_cvv'] ?? '');
    
    // Validate booking
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    
    // Validate amount
    if ($amount != $booking['total_amount']) {
        echo json_encode(['success' => false, 'message' => 'Invalid payment amount']);
        exit;
    }
    
    // Validate payment method
    $valid_methods = ['gcash', 'maya', 'card', 'cash'];
    if (!in_array($payment_method, $valid_methods)) {
        echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
        exit;
    }
    
    // Validate payment method specific data
    if ($payment_method === 'gcash') {
        if (empty($gcash_number) || !preg_match('/^09\d{9}$/', $gcash_number)) {
            echo json_encode(['success' => false, 'message' => 'Invalid GCash number']);
            exit;
        }
    } elseif ($payment_method === 'maya') {
        if (empty($maya_number) || !preg_match('/^09\d{9}$/', $maya_number)) {
            echo json_encode(['success' => false, 'message' => 'Invalid Maya number']);
            exit;
        }
    } elseif ($payment_method === 'card') {
        if (empty($card_number) || empty($card_name) || empty($card_expiry) || empty($card_cvv)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all card details']);
            exit;
        }
        
        // Basic card validation
        $card_number = preg_replace('/\s+/', '', $card_number);
        if (!preg_match('/^\d{13,19}$/', $card_number)) {
            echo json_encode(['success' => false, 'message' => 'Invalid card number']);
            exit;
        }
        
        if (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
            echo json_encode(['success' => false, 'message' => 'Invalid expiry date (MM/YY)']);
            exit;
        }
        
        if (!preg_match('/^\d{3,4}$/', $card_cvv)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CVV']);
            exit;
        }
    }
    
    // Generate payment reference
    $payment_ref = 'PAY' . date('Ymd') . strtoupper(substr(uniqid(), -8));
    
    // In production, integrate with real payment gateway APIs here
    // For now, simulate payment processing
    
    // Store payment details (NEVER store full card details in production!)
    $masked_data = '';
    if ($payment_method === 'gcash') {
        $masked_data = substr($gcash_number, 0, 4) . '****' . substr($gcash_number, -3);
    } elseif ($payment_method === 'maya') {
        $masked_data = substr($maya_number, 0, 4) . '****' . substr($maya_number, -3);
    } elseif ($payment_method === 'card') {
        $masked_data = '**** **** **** ' . substr($card_number, -4);
    }
    
    // Insert payment record
    $stmt = $conn->prepare("INSERT INTO payments 
        (booking_id, payment_ref, payment_method, amount, masked_details, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'completed', NOW())");
    $stmt->bind_param("issds", $booking_id, $payment_ref, $payment_method, $amount, $masked_data);
    
    if ($stmt->execute()) {
        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings 
            SET payment_status = 'paid', 
                payment_method = ?, 
                status = 'confirmed',
                updated_at = NOW() 
            WHERE booking_id = ?");
        $stmt->bind_param("si", $payment_method, $booking_id);
        $stmt->execute();
        
        // Log activity
        log_activity($conn, 'payment_completed', 'payments', $conn->insert_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment successful!',
            'payment_ref' => $payment_ref,
            'booking_ref' => $booking['booking_ref']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>