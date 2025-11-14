/**
 * StayBnB AI Assistant - Client-side Logic
 * Location: js/ai-assistant.js
 */

// User preferences tracking
let userPreferences = {
    tripType: null,
    budget: null,
    location: null,
    interests: []
};

// Sample hotels data (in production, this comes from your database)
const hotels = [
    {
        id: 1,
        name: 'Bataan White Corals Beach Resort',
        location: 'Morong',
        price: 3500,
        rating: 4.5,
        type: 'beach',
        image: 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4',
        amenities: ['Beach Access', 'Swimming Pool', 'Restaurant'],
        nearbySpots: [
            { name: 'Mt. Samat Shrine', distance: 12, type: 'historical', icon: '🏛️' },
            { name: 'Morong Beach', distance: 0.5, type: 'beach', icon: '🏖️' },
            { name: 'Dunsulan Falls', distance: 8, type: 'nature', icon: '🌲' }
        ]
    },
    {
        id: 2,
        name: 'The Plaza Hotel Balanga',
        location: 'Balanga City',
        price: 2500,
        rating: 4.0,
        type: 'business',
        image: 'https://images.unsplash.com/photo-1566073771259-6a8506099945',
        amenities: ['Conference Room', 'WiFi', 'Restaurant'],
        nearbySpots: [
            { name: 'Balanga Capitol', distance: 0.8, type: 'historical', icon: '🏛️' },
            { name: 'Bataan WWII Museum', distance: 1.2, type: 'museum', icon: '🏛️' },
            { name: 'Plaza Mayor', distance: 0.3, type: 'park', icon: '🌳' }
        ]
    },
    {
        id: 3,
        name: 'Las Casas Filipinas de Acuzar',
        location: 'Bagac',
        price: 6000,
        rating: 5.0,
        type: 'heritage',
        image: 'https://images.unsplash.com/photo-1564501049412-61c2a3083791',
        amenities: ['Heritage Tours', 'Museum', 'Swimming Pool'],
        nearbySpots: [
            { name: 'Mt. Samat National Shrine', distance: 15, type: 'historical', icon: '🏛️' },
            { name: 'Bagac Beach', distance: 2, type: 'beach', icon: '🏖️' },
            { name: 'Death March Markers', distance: 5, type: 'historical', icon: '🏛️' }
        ]
    },
    {
        id: 4,
        name: 'Teresita\'s Hotel & Resort',
        location: 'Bagac',
        price: 2000,
        rating: 3.5,
        type: 'leisure',
        image: 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa',
        amenities: ['Swimming Pool', 'Restaurant', 'Parking'],
        nearbySpots: [
            { name: 'Mt. Samat', distance: 18, type: 'historical', icon: '⛰️' },
            { name: 'Bagac Town Center', distance: 1, type: 'shopping', icon: '🛍️' }
        ]
    }
];

// AI Logic: Analyze user input
function analyzeUserInput(text) {
    const lowerText = text.toLowerCase();
    
    // Detect trip type
    if (lowerText.includes('business') || lowerText.includes('work') || lowerText.includes('meeting')) {
        userPreferences.tripType = 'business';
    } else if (lowerText.includes('family') || lowerText.includes('kids') || lowerText.includes('children')) {
        userPreferences.tripType = 'family';
    } else if (lowerText.includes('romantic') || lowerText.includes('honeymoon') || lowerText.includes('couple')) {
        userPreferences.tripType = 'romantic';
    } else if (lowerText.includes('beach') || lowerText.includes('relax') || lowerText.includes('vacation')) {
        userPreferences.tripType = 'leisure';
    } else if (lowerText.includes('history') || lowerText.includes('historical') || lowerText.includes('war') || lowerText.includes('heritage')) {
        userPreferences.tripType = 'historical';
    }
    
    // Detect budget
    if (lowerText.includes('budget') || lowerText.includes('cheap') || lowerText.includes('affordable')) {
        userPreferences.budget = 'low';
    } else if (lowerText.includes('luxury') || lowerText.includes('premium') || lowerText.includes('expensive')) {
        userPreferences.budget = 'high';
    } else if (lowerText.includes('moderate') || lowerText.includes('mid-range')) {
        userPreferences.budget = 'medium';
    }
    
    // Detect location
    const locations = ['morong', 'balanga', 'bagac', 'mariveles', 'limay', 'pilar'];
    locations.forEach(loc => {
        if (lowerText.includes(loc)) {
            userPreferences.location = loc.charAt(0).toUpperCase() + loc.slice(1);
        }
    });
    
    return userPreferences;
}

// AI Recommendation Engine
function getRecommendations(prefs) {
    let scored = hotels.map(hotel => {
        let score = 0;
        
        // Budget matching (30 points)
        if (prefs.budget === 'low' && hotel.price < 2500) score += 30;
        if (prefs.budget === 'medium' && hotel.price >= 2500 && hotel.price <= 4000) score += 30;
        if (prefs.budget === 'high' && hotel.price > 4000) score += 30;
        
        // Location matching (25 points)
        if (prefs.location && hotel.location.toLowerCase().includes(prefs.location.toLowerCase())) {
            score += 25;
        }
        
        // Trip type matching (25 points)
        if (prefs.tripType === 'business' && hotel.type === 'business') score += 25;
        if (prefs.tripType === 'leisure' && hotel.type === 'beach') score += 25;
        if (prefs.tripType === 'historical' && hotel.type === 'heritage') score += 25;
        if (prefs.tripType === 'family' && hotel.type === 'beach') score += 20;
        if (prefs.tripType === 'romantic' && (hotel.type === 'beach' || hotel.type === 'heritage')) score += 20;
        
        // Rating bonus (20 points)
        score += hotel.rating * 4;
        
        return { ...hotel, score };
    });
    
    return scored.sort((a, b) => b.score - a.score).slice(0, 3);
}

// Generate bot response
function generateBotResponse(userMessage) {
    const prefs = analyzeUserInput(userMessage);
    const lowerMsg = userMessage.toLowerCase();
    
    // Greeting responses
    if (lowerMsg.includes('hello') || lowerMsg.includes('hi') || lowerMsg === 'hey') {
        return {
            type: 'text',
            content: "Hello! I'm excited to help you plan your Bataan adventure! 🌴 Are you looking for a beach getaway, historical tour, or business stay?"
        };
    }
    
    // Help request
    if (lowerMsg.includes('help') || lowerMsg.includes('suggest')) {
        return {
            type: 'text',
            content: "I'd love to help! Let me ask you a few questions:\n\n1. What's the purpose of your visit? (business, leisure, family vacation, romantic getaway, or historical tour)\n2. What's your budget range? (budget-friendly, mid-range, or luxury)\n3. Any specific location in Bataan you prefer?"
        };
    }
    
    // If we have trip type, provide recommendations
    if (prefs.tripType) {
        const recommendations = getRecommendations(prefs);
        let response = `Based on what you've told me, I found some perfect matches for your ${prefs.tripType} trip! ✨\n\nHere are my top recommendations:`;
        
        return {
            type: 'recommendations',
            content: response,
            hotels: recommendations
        };
    }
    
    // Default response
    return {
        type: 'text',
        content: "I'm here to help! Could you tell me more about your trip? Are you traveling for business, leisure, with family, or looking for a romantic escape? 💭"
    };
}

// Display message in chat
function displayMessage(content, isUser = false) {
    const chatContainer = document.getElementById('chatContainer');
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
    
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    messageContent.textContent = content;
    
    if (isUser) {
        messageDiv.appendChild(messageContent);
        messageDiv.appendChild(avatar);
    } else {
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(messageContent);
    }
    
    chatContainer.appendChild(messageDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Display hotel recommendations
function displayHotelRecommendations(hotels) {
    const chatContainer = document.getElementById('chatContainer');
    
    hotels.forEach(hotel => {
        const hotelCard = document.createElement('div');
        hotelCard.className = 'hotel-card';
        
        const matchPercentage = Math.round(hotel.score);
        const matchText = hotel.score > 80 ? 'Perfect Match!' : hotel.score > 60 ? 'Great Match!' : 'Good Option';
        
        hotelCard.innerHTML = `
            <img src="${hotel.image}" alt="${hotel.name}">
            <div class="hotel-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="mb-0">${hotel.name}</h5>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star"></i> ${hotel.rating}
                    </span>
                </div>
                
                <p class="text-muted mb-2">
                    <i class="fas fa-map-marker-alt"></i> ${hotel.location}
                </p>
                
                <div class="mb-2">
                    ${hotel.amenities.map(a => `<span class="badge bg-light text-dark me-1">${a}</span>`).join('')}
                </div>
                
                <div class="nearby-spots">
                    <strong style="font-size: 14px;">
                        <i class="fas fa-map-marked-alt"></i> Nearby Attractions:
                    </strong>
                    ${hotel.nearbySpots.slice(0, 3).map(spot => `
                        <div class="spot-item">
                            <span>${spot.icon} ${spot.name}</span>
                            <span class="badge bg-success">${spot.distance}km</span>
                        </div>
                    `).join('')}
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <h4 class="text-primary mb-0">₱${hotel.price.toLocaleString()}</h4>
                        <small class="text-muted">per night</small>
                    </div>
                    <a href="hotel-details.php?id=${hotel.id}" class="btn btn-primary btn-sm">
                        View Details
                    </a>
                </div>
                
                <div class="alert alert-info mt-2 mb-0" style="font-size: 12px;">
                    <i class="fas fa-sparkles"></i> <strong>${matchText}</strong> (${matchPercentage}% match)
                </div>
            </div>
        `;
        
        chatContainer.appendChild(hotelCard);
    });
    
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Show typing indicator
function showTyping() {
    const chatContainer = document.getElementById('chatContainer');
    
    const typingDiv = document.createElement('div');
    typingDiv.className = 'message bot';
    typingDiv.id = 'typingIndicator';
    
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    `;
    
    chatContainer.appendChild(typingDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Remove typing indicator
function removeTyping() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}

// Main send message function
function sendMessage(predefinedMessage = null) {
    const input = document.getElementById('userInput');
    const message = predefinedMessage || input.value.trim();
    
    if (!message) return;
    
    // Display user message
    displayMessage(message, true);
    
    // Clear input
    if (!predefinedMessage) {
        input.value = '';
    }
    
    // Show typing indicator
    showTyping();
    
    // Simulate AI thinking time
    setTimeout(() => {
        removeTyping();
        
        const response = generateBotResponse(message);
        
        // Display bot response
        displayMessage(response.content, false);
        
        // Display hotel recommendations if available
        if (response.type === 'recommendations' && response.hotels) {
            setTimeout(() => {
                displayHotelRecommendations(response.hotels);
            }, 500);
        }
    }, 1500);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Assistant initialized!');
});