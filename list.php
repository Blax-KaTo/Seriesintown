<?php
// Start session and include database connection
session_start();
require_once 'config/db.php';
require_once 'config/api.php';
include("auth_check.php");

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Location: load.php');
}

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
            $user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
            $api_source = "api.themoviedb.org";
            $api_id = filter_var($_POST['api_id'], FILTER_SANITIZE_STRING);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
            $quality = filter_var($_POST['quality'], FILTER_SANITIZE_STRING);
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            
            $allowed_qualities = ['360p', '480p', '720p', '1080p', '4K'];
            if (!in_array($quality, $allowed_qualities)) {
                throw new Exception('Invalid quality selection');
            }

            if ($price <= 0) {
                throw new Exception('Price must be greater than 0');
            }
            
            $db = getDatabaseConnection();

            // Check for duplicate entries
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM movies WHERE user_id = ? AND api_id = ?");
            $check_stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
            $check_stmt->bindValue(2, $api_id, SQLITE3_TEXT);
            $result = $check_stmt->execute()->fetchArray();

            if ($result[0] > 0) {
                throw new Exception('This title is already in your list');
            }

            // Insert new movie/series
            $stmt = $db->prepare("INSERT INTO movies (user_id, type, api_source, api_id, name, quality, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
            $stmt->bindValue(2, $type, SQLITE3_TEXT);
            $stmt->bindValue(3, $api_source, SQLITE3_TEXT);
            $stmt->bindValue(4, $api_id, SQLITE3_TEXT);
            $stmt->bindValue(5, $name, SQLITE3_TEXT);
            $stmt->bindValue(6, $quality, SQLITE3_TEXT);
            $stmt->bindValue(7, $price, SQLITE3_FLOAT);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Added successfully!']);
            } else {
                throw new Exception('Failed to add to database');
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
            display: flex;
            gap: 1em;
        }
        .poster {
            width: 150px;
            height: 225px;
            object-fit: cover;
        }
        .content {
            flex: 1;
        }
        .error {
            color: red;
            display: none;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        .close {
            float: right;
            cursor: pointer;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 1em;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5em;
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

    <!-- Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add to Collection</h2>
            <form id="addForm" onsubmit="event.preventDefault(); submitAdd();">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="add_movie">
                <input type="hidden" name="api_id" id="modal_api_id">
                <input type="hidden" name="name" id="modal_name">
                <input type="hidden" name="type" id="modal_type">
                
                <div class="form-group">
                    <label>Title:</label>
                    <span id="modal_title_display"></span>
                </div>
                
                <div class="form-group">
                    <label>Type:</label>
                    <span id="modal_type_display"></span>
                </div>

                <div class="form-group">
                    <label for="modal_price">Price:</label>
                    <input type="number" id="modal_price" name="price" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="modal_quality">Quality:</label>
                    <select name="quality" id="modal_quality" required>
                        <option value="360p">360p</option>
                        <option value="480p">480p</option>
                        <option value="720p">720p</option>
                        <option value="1080p">1080p</option>
                        <option value="4K">4K</option>
                    </select>
                </div>

                <button type="submit">Add to Collection</button>
            </form>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';
        
        async function searchTMDB(query) {
            try {
                const response = await fetch(`https://api.themoviedb.org/3/search/multi?api_key=<?= htmlspecialchars(TMDB_API_KEY) ?>&query=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Search failed');
                const data = await response.json();
                displayResults(data.results);
            } catch (error) {
                showError('Failed to search. Please try again later.');
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
                const posterPath = result.poster_path ? 
                    `https://image.tmdb.org/t/p/w500${result.poster_path}` : 
                    'placeholder-image.jpg';
                
                resultDiv.innerHTML = `
                    <img src="${posterPath}" alt="${title}" class="poster">
                    <div class="content">
                        <h3>${title}</h3>
                        <p>${overview}</p>
                        <button onclick="playTrailer('${result.id}', '${result.media_type}')">Play Trailer</button>
                        <button onclick="openAddModal('${result.id}', '${escapeHtml(title)}', '${result.media_type}')">
                            Add to Collection
                        </button>
                    </div>
                `;
                resultsContainer.appendChild(resultDiv);
            });
        }

        function openAddModal(apiId, title, type) {
            document.getElementById('modal_api_id').value = apiId;
            document.getElementById('modal_name').value = title;
            document.getElementById('modal_type').value = type;
            document.getElementById('modal_title_display').textContent = title;
            document.getElementById('modal_type_display').textContent = type === 'movie' ? 'Movie' : 'TV Series';
            document.getElementById('addModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addForm').reset();
        }

        async function submitAdd() {
            try {
                const form = document.getElementById('addForm');
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    closeModal();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                showError(error.message || 'Failed to add to collection');
            }
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