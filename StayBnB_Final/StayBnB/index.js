// Search and Maps Functionality
function showSuggestions() {
    const searchInput = document.getElementById('destinationSearch');
    const suggestions = document.getElementById('searchSuggestions');
    
    if (searchInput.value.length > 0) {
        suggestions.style.display = 'block';
    } else {
        suggestions.style.display = 'none';
    }
}

function selectSuggestion(destination) {
    document.getElementById('destinationSearch').value = destination;
    document.getElementById('searchSuggestions').style.display = 'none';
    searchDestination();
}

function searchDestination() {
    const searchInput = document.getElementById('destinationSearch');
    const destination = searchInput.value.trim().toLowerCase();
    
    if (destination === '') {
        alert('Please enter a destination to search');
        return;
    }
    
    // Scroll to maps section first
    const mapSection = document.getElementById('php-map-section');
    if (mapSection) {
        mapSection.scrollIntoView({
            behavior: 'smooth'
        });
    }
    
    // Update map based on search
    updateMap(destination);
}

// NEW: Simple Search Location Function for Search Bar
function searchLocation() {
    const searchInput = document.getElementById('searchInput');
    const destination = searchInput.value.trim().toLowerCase();
    
    if (destination === '') {
        alert('Please enter a destination to search');
        return;
    }
    
    // Scroll to maps section
    const mapSection = document.getElementById('php-map-section');
    if (mapSection) {
        mapSection.scrollIntoView({
            behavior: 'smooth'
        });
        
        // Update map after scroll
        setTimeout(() => {
            updateMap(destination);
        }, 500);
    } else {
        updateMap(destination);
    }
}

// Add Enter key support for search bar
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchLocation();
            }
        });
    }
});

function updateMap(destination) {
    const mapIframe = document.querySelector('.main-map');
    if (!mapIframe) {
        console.error('Map iframe not found');
        return;
    }
    
    // Define coordinates and exact tourist spots coordinates - UPDATED FOR BATAAN
    const locations = {
        'bataan': {
            coords: '14.6983,120.2674',
            spots: [
                {name: 'Las Casas Filipinas de Acuzar', coords: '14.6472,120.2789', type: 'beach'},
                {name: 'Camaya Coast Beach Resort', coords: '14.4333,120.4833', type: 'beach'},
                {name: 'Bataan People\'s Center Beach', coords: '14.4500,120.4667', type: 'beach'},
                {name: 'Five Fingers Cove Resort', coords: '14.4167,120.4667', type: 'beach'},
                {name: 'Mt. Samat National Shrine', coords: '14.6042,120.5014', type: 'tourist'},
                {name: 'Bataan National Park', coords: '14.5000,120.4833', type: 'tourist'},
                {name: 'Balanga Wetland Park', coords: '14.6833,120.5333', type: 'tourist'},
                {name: 'Dunsulan Falls', coords: '14.7833,120.4167', type: 'tourist'},
                {name: 'Mariveles Five Fingers', coords: '14.4333,120.4833', type: 'tourist'},
                {name: 'Pawikan Conservation Center', coords: '14.4333,120.4500', type: 'tourist'}
            ]
        },
        'cebu': {
            coords: '10.3157,123.8854',
            spots: [
                {name: 'Magellan\'s Cross', coords: '10.2929,123.9021', type: 'tourist'},
                {name: 'Taoist Temple', coords: '10.3494,123.9173', type: 'tourist'},
                {name: 'Kawasan Falls', coords: '9.8037,123.3722', type: 'tourist'},
                {name: 'Oslob Whale Sharks', coords: '9.5167,123.4000', type: 'tourist'},
                {name: 'Fort San Pedro', coords: '10.2922,123.9047', type: 'tourist'}
            ]
        },
        'boracay': {
            coords: '11.9674,121.9248',
            spots: [
                {name: 'White Beach', coords: '11.9584,121.9271', type: 'beach'},
                {name: 'Puka Shell Beach', coords: '11.9833,121.9333', type: 'beach'},
                {name: 'Mount Luho', coords: '11.9719,121.9319', type: 'tourist'},
                {name: 'Bulabog Beach', coords: '11.9631,121.9300', type: 'beach'},
                {name: 'Crystal Cove Island', coords: '11.9000,121.9167', type: 'tourist'}
            ]
        },
        'palawan': {
            coords: '9.8349,118.7384',
            spots: [
                {name: 'Underground River', coords: '10.1928,118.8969', type: 'tourist'},
                {name: 'El Nido Big Lagoon', coords: '11.1917,119.4014', type: 'tourist'},
                {name: 'Coron Shipwrecks', coords: '11.9986,120.0208', type: 'tourist'},
                {name: 'Tubbataha Reef', coords: '8.9500,119.8667', type: 'tourist'},
                {name: 'Nacpan Beach', coords: '11.2000,119.4167', type: 'beach'}
            ]
        },
        'manila': {
            coords: '14.5995,120.9842',
            spots: [
                {name: 'Intramuros', coords: '14.5912,120.9739', type: 'tourist'},
                {name: 'Rizal Park', coords: '14.5822,120.9769', type: 'tourist'},
                {name: 'National Museum', coords: '14.5869,120.9814', type: 'tourist'},
                {name: 'Binondo Chinatown', coords: '14.6011,120.9725', type: 'tourist'},
                {name: 'Mall of Asia', coords: '14.5356,120.9822', type: 'tourist'}
            ]
        },
        'siargao': {
            coords: '9.8297,126.0551',
            spots: [
                {name: 'Cloud 9 Surfing', coords: '9.8581,126.0464', type: 'beach'},
                {name: 'Sugba Lagoon', coords: '9.9167,126.0667', type: 'tourist'},
                {name: 'Magpupungko Rock Pools', coords: '9.7833,126.1500', type: 'tourist'},
                {name: 'Guyam Island', coords: '9.8500,126.0833', type: 'beach'},
                {name: 'Daku Island', coords: '9.8667,126.1000', type: 'beach'}
            ]
        },
        'elnido': {
            coords: '11.1853,119.4063',
            spots: [
                {name: 'Big Lagoon', coords: '11.1917,119.4014', type: 'tourist'},
                {name: 'Small Lagoon', coords: '11.1931,119.4000', type: 'tourist'},
                {name: 'Secret Beach', coords: '11.1833,119.3833', type: 'beach'},
                {name: 'Seven Commandos Beach', coords: '11.2000,119.4167', type: 'beach'},
                {name: 'Shimizu Island', coords: '11.1833,119.4000', type: 'tourist'}
            ]
        },
        'bohol': {
            coords: '9.8492,124.1440',
            spots: [
                {name: 'Chocolate Hills', coords: '9.8333,124.1667', type: 'tourist'},
                {name: 'Tarsier Sanctuary', coords: '9.7000,124.1167', type: 'tourist'},
                {name: 'Loboc River Cruise', coords: '9.6333,124.0333', type: 'tourist'},
                {name: 'Panglao Beach', coords: '9.5667,123.7667', type: 'beach'},
                {name: 'Blood Compact Shrine', coords: '9.6333,123.8500', type: 'tourist'}
            ]
        },
        'baguio': {
            coords: '16.4023,120.5960',
            spots: [
                {name: 'Burnham Park', coords: '16.4128,120.5972', type: 'tourist'},
                {name: 'Mines View Park', coords: '16.4167,120.6167', type: 'tourist'},
                {name: 'The Mansion', coords: '16.4083,120.6083', type: 'tourist'},
                {name: 'Botanical Garden', coords: '16.4167,120.6000', type: 'tourist'},
                {name: 'Strawberry Farms', coords: '16.4167,120.6167', type: 'tourist'}
            ]
        },
        'vigan': {
            coords: '17.5745,120.3869',
            spots: [
                {name: 'Calle Crisologo', coords: '17.5747,120.3881', type: 'tourist'},
                {name: 'Bantay Bell Tower', coords: '17.5833,120.3833', type: 'tourist'},
                {name: 'Syquia Mansion', coords: '17.5750,120.3889', type: 'tourist'},
                {name: 'Vigan Cathedral', coords: '17.5742,120.3886', type: 'tourist'},
                {name: 'Hidden Garden', coords: '17.5667,120.3833', type: 'tourist'}
            ]
        }
    };
    
    // Get location data or use default (Bataan)
    const locationData = locations[destination] || locations['bataan'];
    const coords = locationData.coords;
    
    // Create Google Maps URL with markers for all tourist spots
    let markersUrl = '';
    locationData.spots.forEach((spot, index) => {
        const color = spot.type === 'beach' ? 'blue' : 'red';
        markersUrl += `&markers=color:${color}%7Clabel:${index + 1}%7C${spot.coords}`;
    });
    
    // Update map URL to show tourist spots with markers
    // Using free Google Maps embed
    if (destination === 'bataan') {
        mapUrl = `https://maps.google.com/maps?q=bataan+beach+resorts+tourist+spots&t=k&z=11&output=embed&ie=UTF8`;
    } else {
        mapUrl = `https://maps.google.com/maps?q=${destination}+philippines+tourist+spots&t=k&z=12&output=embed&ie=UTF8`;
    }
    
    // Update the iframe source
    mapIframe.src = mapUrl;
    
    // Update location info and tourist spots
    updateLocationInfo(destination, locationData.spots);
}

function updateLocationInfo(destination, touristSpots) {
    const locationTitle = document.querySelector('.location-info h3');
    const locationInfo = document.querySelector('.info-content p');
    
    if (!locationTitle || !locationInfo) {
        console.error('Location info elements not found');
        return;
    }
    
    const locations = {
        'bataan': {
            title: 'Bataan - Beaches & Resorts',
            address: 'Bataan Province, Philippines<br>🏖️ Blue markers: Beaches & Resorts<br>🏛️ Red markers: Tourist Spots'
        },
        'cebu': {
            title: 'Cebu Tourist Spots',
            address: 'Cebu City, Philippines<br>📍 Explore top attractions in Cebu'
        },
        'boracay': {
            title: 'Boracay Island',
            address: 'Malay, Aklan<br>🏖️ Blue markers: Beaches<br>🏛️ Red markers: Tourist Spots'
        },
        'palawan': {
            title: 'Palawan Paradise',
            address: 'Puerto Princesa, Palawan<br>🏖️ Blue markers: Beaches<br>🏛️ Red markers: Tourist Spots'
        },
        'manila': {
            title: 'Manila City Tour',
            address: 'Metro Manila<br>📍 Historical and cultural sites'
        },
        'siargao': {
            title: 'Siargao Surf Capital',
            address: 'Surigao del Norte<br>🏖️ Blue markers: Beaches<br>🏛️ Red markers: Tourist Spots'
        },
        'elnido': {
            title: 'El Nido Palawan',
            address: 'El Nido, Palawan<br>🏖️ Blue markers: Beaches<br>🏛️ Red markers: Tourist Spots'
        },
        'bohol': {
            title: 'Bohol Nature Tour',
            address: 'Tagbilaran, Bohol<br>🏖️ Blue markers: Beaches<br>🏛️ Red markers: Tourist Spots'
        },
        'baguio': {
            title: 'Baguio Summer Capital',
            address: 'Benguet Province<br>📍 Cool climate and scenic parks'
        },
        'vigan': {
            title: 'Vigan Heritage',
            address: 'Ilocos Sur<br>📍 Spanish colonial architecture'
        }
    };
    
    const location = locations[destination] || locations['bataan'];
    
    locationTitle.textContent = location.title;
    locationInfo.innerHTML = location.address;
    
    // Update tourist spots list with numbers matching map markers
    updateTouristSpots(touristSpots);
}

function updateTouristSpots(spots) {
    // Separate beaches and tourist spots
    const beaches = spots.filter(spot => spot.type === 'beach');
    const touristSpots = spots.filter(spot => spot.type === 'tourist');
    
    const spotsHTML = `
        <div class="tourist-spots" id="touristSpots">
            ${beaches.length > 0 ? `
            <div class="spots-category">
                <h4>🏖️ Beaches & Resorts:</h4>
                <div class="spots-list">
                    ${beaches.map((spot, index) => 
                        `<div class="spot-item beach-spot">
                            <span class="marker-number beach-marker">${spots.indexOf(spot) + 1}</span>
                            ${spot.name}
                        </div>`
                    ).join('')}
                </div>
            </div>
            ` : ''}
            
            ${touristSpots.length > 0 ? `
            <div class="spots-category">
                <h4>🏛️ Tourist Spots:</h4>
                <div class="spots-list">
                    ${touristSpots.map((spot, index) => 
                        `<div class="spot-item tourist-spot">
                            <span class="marker-number tourist-marker">${spots.indexOf(spot) + 1}</span>
                            ${spot.name}
                        </div>`
                    ).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    const existingSpots = document.getElementById('touristSpots');
    const locationInfo = document.querySelector('.location-info');
    
    if (!locationInfo) {
        console.error('Location info container not found');
        return;
    }
    
    if (existingSpots) {
        existingSpots.outerHTML = spotsHTML;
    } else {
        locationInfo.insertAdjacentHTML('beforeend', spotsHTML);
    }
}

document.addEventListener('click', function(e) {
    const suggestions = document.getElementById('searchSuggestions');
    const searchInput = document.getElementById('destinationSearch');
    
    if (suggestions && searchInput && 
        !e.target.closest('.search-form') && 
        !e.target.closest('#searchSuggestions')) {
        suggestions.style.display = 'none';
    }
});

function addTouristSpotsCSS() {
    if (!document.querySelector('#touristSpotsStyle')) {
        const style = document.createElement('style');
        style.id = 'touristSpotsStyle';
        style.textContent = `
            .tourist-spots {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 2px solid #f0f0f0;
            }
            
            .spots-category {
                margin-bottom: 25px;
            }
            
            .spots-category h4 {
                color: #2c3e50;
                margin-bottom: 15px;
                font-size: 1.2rem;
            }
            
            .spots-list {
                display: grid;
                gap: 10px;
            }
            
            .spot-item {
                padding: 10px 15px;
                background: #f8f9fa;
                border-radius: 8px;
                color: #5d6d7e;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .beach-spot {
                border-left: 4px solid #3498db;
            }
            
            .tourist-spot {
                border-left: 4px solid #e74c3c;
            }
            
            .marker-number {
                color: white;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            .beach-marker {
                background: #3498db;
            }
            
            .tourist-marker {
                background: #e74c3c;
            }
            
            .search-suggestions {
                display: none;
                position: absolute;
                background: white;
                width: 100%;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                z-index: 1000;
                max-height: 200px;
                overflow-y: auto;
            }
            
            .suggestion-item {
                padding: 12px 20px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background 0.2s ease;
            }
            
            .suggestion-item:hover {
                background: #f8f9fa;
            }
            
            .suggestion-item:last-child {
                border-bottom: none;
            }
        `;
        document.head.appendChild(style);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addTouristSpotsCSS();
});

// Make functions globally available
window.showSuggestions = showSuggestions;
window.selectSuggestion = selectSuggestion;
window.searchDestination = searchDestination;
window.searchLocation = searchLocation; // NEW: Added searchLocation function
window.updateMap = updateMap;
window.updateLocationInfo = updateLocationInfo;
window.updateTouristSpots = updateTouristSpots;

//Flip card JS//

function flipCard(card) {
    card.classList.toggle('active');
}

// Optional: Add click event listeners to all flip cards
document.querySelectorAll('.flip-card').forEach(card => {
    card.addEventListener('click', function() {
        this.classList.toggle('active');
    });
});

//Rating Container//

document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const submitBtn = document.getElementById('submit-rating');
            const thankYouMessage = document.getElementById('thank-you');
            const userNameInput = document.getElementById('user-name');
            let selectedRating = 0;
            
            // Star rating functionality
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = parseInt(this.getAttribute('data-value'));
                    selectedRating = value;
                    
                    // Update stars appearance
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                    
                    // Enable submit button if name is provided
                    if (userNameInput.value.trim() !== '') {
                        submitBtn.disabled = false;
                    }
                });
            });
            
            // Enable submit button when name is entered
            userNameInput.addEventListener('input', function() {
                if (this.value.trim() !== '' && selectedRating > 0) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            });
            
            // Submit rating
            submitBtn.addEventListener('click', function() {
                if (selectedRating > 0 && userNameInput.value.trim() !== '') {
                    // In a real application, you would send this data to a server
                    console.log('Rating submitted:', {
                        rating: selectedRating,
                        name: userNameInput.value.trim(),
                        comment: document.getElementById('user-comment').value.trim()
                    });
                    
                    // Show thank you message
                    thankYouMessage.style.display = 'block';
                    
                    // Reset form
                    stars.forEach(star => star.classList.remove('active'));
                    userNameInput.value = '';
                    document.getElementById('user-comment').value = '';
                    submitBtn.disabled = true;
                    selectedRating = 0;
                    
                    // Hide thank you message after 5 seconds
                    setTimeout(() => {
                        thankYouMessage.style.display = 'none';
                    }, 5000);
                }
            });
        });

