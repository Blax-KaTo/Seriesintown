<?php
session_start();
require_once 'config/db.php'; // Include database connection

// Initialize error message
$errorMsg = "";

// Handle Confirm Town button click
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from POST request
    $userId = $_SESSION['user_id']; // Assuming user ID is stored in the session
    $town = $_POST['town'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    // Insert into the database
    $db = new SQLite3('config/db.php');
    $stmt = $db->prepare("INSERT INTO town (user_id, town, latitude, longitude) VALUES (:user_id, :town, :latitude, :longitude)");
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $stmt->bindValue(':town', $town, SQLITE3_TEXT);
    $stmt->bindValue(':latitude', $latitude, SQLITE3_FLOAT);
    $stmt->bindValue(':longitude', $longitude, SQLITE3_FLOAT);

    // Execute and check if successful
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        $errorMsg = "Failed to save location. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Town - SeriesInTown</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <style>
        /* Your existing CSS */
    </style>
</head>
<body>
    <!-- Existing HTML content -->
    <div class="header">
        <h1>Get Town.</h1>
        <button onclick="getCurrentLocation()" style="border: none; background: none; color: #007AFF; font-size: 15px; font-weight: 500;">
            Refresh
        </button>
    </div>

    <div id="map"></div>

    <div class="location-card">
        <div class="drag-handle"></div>
        <div class="location-info">
            <div class="location-label">Your Town.</div>
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
        <form method="POST" id="locationForm">
            <input type="hidden" name="town" id="townInput">
            <input type="hidden" name="latitude" id="latitudeInput">
            <input type="hidden" name="longitude" id="longitudeInput">
            <button type="button" class="action-button" onclick="confirmLocation()">Confirm Town</button>
        </form>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Getting your location...</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.accurateposition/Leaflet.AccuratePosition.js"></script>
    <script>
        let map, marker, circle, currentLocation = null;

        function initMap() {
            map = L.map('map').setView([0, 0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            getCurrentLocation();
        }

        async function getCurrentLocation() {
            document.getElementById('loadingOverlay').style.display = 'flex';
            map.locate({ enableHighAccuracy: true }).on('locationfound', handleLocationFound).on('locationerror', handleLocationError);
        }

        function handleLocationFound(e) {
            currentLocation = e.latlng;
            if (marker) marker.remove();
            if (circle) circle.remove();
            marker = L.marker(e.latlng).addTo(map);
            circle = L.circle(e.latlng, { radius: e.accuracy / 2 }).addTo(map);
            map.setView(e.latlng, 16);

            document.getElementById('coordinates').textContent = `${e.latlng.lat.toFixed(6)}° N, ${e.latlng.lng.toFixed(6)}° E`;
            document.getElementById('latitudeInput').value = e.latlng.lat;
            document.getElementById('longitudeInput').value = e.latlng.lng;
            document.getElementById('cityName').textContent = "Your Detected Town"; // Replace with actual town name logic if needed
            document.getElementById('townInput').value = "Detected Town"; // Replace with actual town name
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function confirmLocation() {
            document.getElementById('locationForm').submit();
        }

        function handleLocationError(e) {
            alert("Failed to get location.");
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        initMap();
    </script>
</body>
</html>
