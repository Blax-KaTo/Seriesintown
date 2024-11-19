<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location - SpotsInTown</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 16px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 17px;
            font-weight: 600;
        }

        #map {
            flex: 1;
            width: 100%;
            margin-top: 60px;
            z-index: 1;
        }

        .location-card {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(85%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
        }

        .location-card.expanded {
            transform: translateY(0);
        }

        .drag-handle {
            width: 40px;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            margin: 0 auto 20px;
        }

        .location-info {
            margin-bottom: 20px;
        }

        .location-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 4px;
        }

        .location-value {
            font-size: 17px;
            font-weight: 500;
            color: #1d1d1f;
        }

        .action-button {
            width: 100%;
            padding: 16px;
            background: #007AFF;
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 17px;
            font-weight: 600;
            margin-top: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .action-button:active {
            transform: scale(0.98);
        }

        .accuracy-indicator {
            display: flex;
            align-items: center;
            margin-top: 12px;
            gap: 8px;
        }

        .accuracy-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ddd;
        }

        .accuracy-dot.active {
            background: #34C759;
        }

        .accuracy-label {
            font-size: 13px;
            color: #666;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007AFF;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }

        .loading-text {
            font-size: 17px;
            color: #1d1d1f;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Set Your Location</h1>
        <button onclick="getCurrentLocation()" style="border: none; background: none; color: #007AFF; font-size: 15px; font-weight: 500;">
            Refresh
        </button>
    </div>

    <div id="map"></div>

    <div class="location-card">
        <div class="drag-handle"></div>
        <div class="location-info">
            <div class="location-label">Current Location</div>
            <div class="location-value" id="cityName">Detecting location...</div>
            <div class="accuracy-indicator">
                <div class="accuracy-dot" id="accuracyDot"></div>
                <div class="accuracy-label" id="accuracyLabel">Accuracy: Calculating...</div>
            </div>
        </div>
        <div class="location-info">
            <div class="location-label">Coordinates</div>
            <div class="location-value" id="coordinates">--° N, --° E</div>
        </div>
        <button class="action-button" onclick="confirmLocation()">
            Confirm Location
        </button>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Getting your location...</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.accurateposition/Leaflet.AccuratePosition.js"></script>
    <script>
        let map, marker, circle;
        let currentLocation = null;

        // Initialize map
        function initMap() {
            map = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView([0, 0], 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            // Initialize location card drag behavior
            initDragBehavior();
        }

        // Initialize drag behavior for location card
        function initDragBehavior() {
            const card = document.querySelector('.location-card');
            const handle = document.querySelector('.drag-handle');
            let startY, startTransform;

            handle.addEventListener('touchstart', (e) => {
                startY = e.touches[0].clientY;
                startTransform = card.style.transform ? 
                    parseInt(card.style.transform.replace('translateY(', '')) :
                    85;
                e.preventDefault();
            });

            handle.addEventListener('touchmove', (e) => {
                const deltaY = e.touches[0].clientY - startY;
                const newTransform = Math.max(0, Math.min(85, startTransform + (deltaY / window.innerHeight * 100)));
                card.style.transform = `translateY(${newTransform}%)`;
            });

            handle.addEventListener('touchend', () => {
                const currentTransform = parseInt(card.style.transform.replace('translateY(', ''));
                if (currentTransform < 40) {
                    card.style.transform = 'translateY(0%)';
                } else {
                    card.style.transform = 'translateY(85%)';
                }
            });
        }

        // Get current location with high accuracy
        async function getCurrentLocation() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            try {
                map.locate({
                    enableHighAccuracy: true,
                    accuratePosition: true,
                    maxZoom: 16,
                    watch: false,
                    timeout: 30000
                });

                map.on('locationfound', handleLocationFound);
                map.on('locationerror', handleLocationError);
            } catch (error) {
                handleLocationError(error);
            }
        }

        // Handle successful location retrieval
        async function handleLocationFound(e) {
            currentLocation = e.latlng;
            const accuracy = Math.round(e.accuracy);

            // Update map
            if (marker) marker.remove();
            if (circle) circle.remove();

            marker = L.marker(e.latlng).addTo(map);
            circle = L.circle(e.latlng, {
                radius: e.accuracy / 2,
                color: '#007AFF',
                fillColor: '#007AFF',
                fillOpacity: 0.1
            }).addTo(map);

            map.setView(e.latlng, 16);

            // Update UI
            document.getElementById('coordinates').textContent = 
                `${e.latlng.lat.toFixed(6)}° N, ${e.latlng.lng.toFixed(6)}° E`;
            
            document.getElementById('accuracyDot').classList.add('active');
            document.getElementById('accuracyLabel').textContent = 
                `Accuracy: ${accuracy < 1000 ? accuracy + ' meters' : (accuracy/1000).toFixed(1) + ' km'}`;

            // Get city name
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${e.latlng.lat}&lon=${e.latlng.lng}&format=json`
                );
                const data = await response.json();
                const cityName = data.address.city || data.address.town || data.address.village || 'Unknown location';
                document.getElementById('cityName').textContent = cityName;
            } catch (error) {
                document.getElementById('cityName').textContent = 'Location found (City name unavailable)';
            }

            document.getElementById('loadingOverlay').style.display = 'none';
            document.querySelector('.location-card').classList.add('expanded');
        }

        // Handle location errors
        function handleLocationError(e) {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('cityName').textContent = 'Location error. Please try again.';
            document.getElementById('accuracyDot').classList.remove('active');
            document.getElementById('accuracyLabel').textContent = 'Accuracy: Unknown';
            alert('Could not get your location. Please check your permissions and try again.');
        }

        // Confirm location and proceed
        function confirmLocation() {
            if (!currentLocation) {
                alert('Please wait for your location to be detected.');
                return;
            }
            
            // Store location data and proceed to next page
            localStorage.setItem('userLocation', JSON.stringify({
                lat: currentLocation.lat,
                lng: currentLocation.lng,
                city: document.getElementById('cityName').textContent
            }));
            
            window.location.href = 'main.html';
        }

        // Initialize map on load
        initMap();
        getCurrentLocation();
    </script>
</body>
</html>