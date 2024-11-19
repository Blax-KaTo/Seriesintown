<?php
// Start session and include database connection
session_start();
require_once 'config/db.php'; // Adjust path as necessary

include("auth_check.php");

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Please log in to continue.');
}

// Move API key to configuration file
require_once 'config/api.php'; // Define TMDB_API_KEY here instead of in main file

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('CSRF token validation failed');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_movie') {
        try {
            // Sanitize and validate inputs
            $user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
            $api_id = filter_var($_POST['api_id'], FILTER_SANITIZE_STRING);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $source = filter_var($_POST['source'], FILTER_SANITIZE_STRING);
            $quality = filter_var($_POST['quality'], FILTER_SANITIZE_STRING);
            
            // Validate quality options
            $allowed_qualities = ['360p', '480p', '720p', '1080p', '4K'];
            if (!in_array($quality, $allowed_qualities)) {
                throw new Exception('Invalid quality selection');
            }

            $price = 0.0; // Replace with actual price calculation if needed
            
            
            $db = getDatabaseConnection();

            // Check for duplicate entries
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM movies WHERE user_id = ? AND api_id = ?");
            $check_stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
            $check_stmt->bindValue(2, $api_id, SQLITE3_TEXT);
            $result = $check_stmt->execute()->fetchArray();

            if ($result[0] > 0) {
                throw new Exception('This movie is already in your list');
            }
            

            // Insert new movie
            $stmt = $db->prepare("INSERT INTO movies (user_id, api_source, api_id, name, quality, price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
            $stmt->bindValue(2, $source, SQLITE3_TEXT);
            $stmt->bindValue(3, $api_id, SQLITE3_TEXT);
            $stmt->bindValue(4, $name, SQLITE3_TEXT);
            $stmt->bindValue(5, $quality, SQLITE3_TEXT);
            $stmt->bindValue(6, $price, SQLITE3_FLOAT);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Movie or series added successfully!']);
            } else {
                throw new Exception('Failed to add movie to database');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie or Series</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <style>
        .result-item {
            margin: 1em 0;
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: red;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Add a Movie or Series</h1>
    <form id="searchForm" onsubmit="event.preventDefault(); searchTMDB(document.getElementById('search').value);">
        <input type="text" id="search" placeholder="Search for movies or series..." required minlength="2">
        <button type="submit">Search</button>
    </form>
    <div id="error" class="error"></div>
    <div id="results"></div>

    <script>
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';
        
        async function searchTMDB(query) {
            try {
                const response = await fetch(`https://api.themoviedb.org/3/search/multi?api_key=<?= htmlspecialchars(TMDB_API_KEY) ?>&query=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Search failed');
                const data = await response.json();
                displayResults(data.results);
            } catch (error) {
                showError('Failed to search movies. Please try again later.');
            }
        }

        function displayResults(results) {
            const resultsContainer = document.getElementById("results");
            const errorDiv = document.getElementById("error");
            errorDiv.style.display = 'none';
            resultsContainer.innerHTML = '';

            if (!results || results.length === 0) {
                showError('No results found');
                return;
            }

            results.forEach(result => {
                if (result.media_type !== 'movie' && result.media_type !== 'tv') return;
                
                const resultDiv = document.createElement("div");
                resultDiv.classList.add("result-item");

                const title = escapeHtml(result.media_type === 'movie' ? result.title : result.name);
                const overview = escapeHtml(result.overview || 'No description available.');
                
                resultDiv.innerHTML = `
                    <h3>${title}</h3>
                    <p>${overview}</p>
                    <button onclick="playTrailer('${result.id}', '${result.media_type}')">Play Trailer</button>
                    <form onsubmit="event.preventDefault(); addMovie(this);">
                        <input type="hidden" name="csrf_token" value="${CSRF_TOKEN}">
                        <input type="hidden" name="action" value="add_movie">
                        <input type="hidden" name="api_id" value="${result.id}">
                        <input type="hidden" name="name" value="${title}">
                        <input type="hidden" name="source" value="${result.media_type}">
                        <label for="quality_${result.id}">Quality:</label>
                        <select name="quality" id="quality_${result.id}">
                            <option value="360p">360p</option>
                            <option value="480p">480p</option>
                            <option value="720p">720p</option>
                            <option value="1080p">1080p</option>
                            <option value="4K">4K</option>
                        </select>
                        <button type="submit">Add Movie or Series</button>
                    </form>
                `;
                resultsContainer.appendChild(resultDiv);
            });
        }

        async function playTrailer(id, type) {
            try {
                const response = await fetch(`https://api.themoviedb.org/3/${type}/${id}/videos?api_key=<?= htmlspecialchars(TMDB_API_KEY) ?>`);
                if (!response.ok) throw new Error('Failed to fetch trailer');
                const data = await response.json();
                const trailer = data.results.find(video => video.type === 'Trailer' && video.site === 'YouTube');
                if (trailer) {
                    window.open(`https://www.youtube.com/watch?v=${trailer.key}`, '_blank');
                } else {
                    showError('Trailer not available');
                }
            } catch (error) {
                showError('Failed to load trailer');
            }
        }

        async function addMovie(form) {
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    form.reset();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showError(error.message || 'Failed to add movie');
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById("error");
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>