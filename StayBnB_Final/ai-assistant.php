<?php
/**
 * StayBnB - AI Assistant Page
 * Location: ai-assistant.php (CREATE THIS FILE IN ROOT FOLDER)
 */

define('STAYBNB_ACCESS', true);
require_once 'config/db_connect.php';

// Optional: Require login (remove if you want it accessible to everyone)
// require_login();

$page_title = 'AI Travel Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - StayBnB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .ai-container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .ai-header {
            background: linear-gradient(135deg, #0a53fe 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .ai-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        
        .chat-container {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .message.bot {
            justify-content: flex-start;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .bot .message-avatar {
            background: linear-gradient(135deg, #0a53fe, #764ba2);
            color: white;
        }
        
        .user .message-avatar {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .message-content {
            max-width: 70%;
            padding: 15px;
            border-radius: 15px;
        }
        
        .bot .message-content {
            background: white;
            border: 1px solid #e5e7eb;
        }
        
        .user .message-content {
            background: #0a53fe;
            color: white;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 20px;
            background: white;
        }
        
        .quick-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #0a53fe;
            background: white;
            color: #0a53fe;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .quick-btn:hover {
            background: #0a53fe;
            color: white;
        }
        
        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }
        
        .hotel-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 15px;
        }
        
        .hotel-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .hotel-card-body {
            padding: 15px;
        }
        
        .nearby-spots {
            background: #f0fdf4;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }
        
        .spot-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 13px;
        }
        
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 10px;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6b7280;
            animation: typing 1.4s infinite;
        }
        
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link active" href="ai-assistant.php">AI Assistant</a></li>
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="ai-container">
        <!-- Header -->
        <div class="ai-header">
            <i class="fas fa-robot fa-3x mb-3"></i>
            <h1>AI Travel Assistant</h1>
            <p class="mb-0">Your intelligent guide to finding the perfect hotel in Bataan</p>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="quick-btn" onclick="sendMessage('I want a beach resort for family vacation')">
                🏖️ Beach Family Vacation
            </button>
            <button class="quick-btn" onclick="sendMessage('Looking for budget-friendly hotel in Balanga')">
                💰 Budget Hotel
            </button>
            <button class="quick-btn" onclick="sendMessage('I want to visit historical sites')">
                🏛️ Historical Tour
            </button>
            <button class="quick-btn" onclick="sendMessage('Luxury romantic getaway')">
                💕 Romantic Stay
            </button>
        </div>

        <!-- Chat Container -->
        <div class="chat-container" id="chatContainer">
            <div class="message bot">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    Hello! 👋 I'm your StayBnB AI Assistant. I can help you find the perfect hotel in Bataan based on your preferences. What brings you to Bataan?
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-group">
                <input type="text" 
                       id="userInput" 
                       class="form-control" 
                       placeholder="Tell me what you're looking for..."
                       onkeypress="if(event.key==='Enter') sendMessage()">
                <button class="btn btn-primary" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/ai-assistant.js"></script>
</body>
</html>
<?php $conn->close(); ?>