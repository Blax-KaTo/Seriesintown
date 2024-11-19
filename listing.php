<?php
require_once 'config/db.php';
include("auth_check.php");

// TMDB API Configuration
define('TMDB_API_KEY', 'ff3be76dc0cdcf90e42c31f9fcdd2cd8');
define('TMDB_API_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMG_BASE_URL', 'https://image.tmdb.org/t/p/w500');

function fetchTMDBDetails($apiId, $type) {
    $endpoint = $type === 'movie' ? 'movie' : 'tv';
    $url = TMDB_API_BASE_URL . "/{$endpoint}/{$apiId}?api_key=" . TMDB_API_KEY;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data;
    }
    
    return null;
}

// Get user's content from database
$userId = $_SESSION['user_id'];
$db = getDatabaseConnection();

// Fetch basic info from database
$moviesStmt = $db->prepare("
    SELECT api_id, type, quality, price 
    FROM movies 
    WHERE user_id = :user_id 
    ORDER BY timestamp DESC
");
$moviesStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$moviesResult = $moviesStmt->execute();

// Fetch and enrich with TMDB details
$movies = [];
while ($row = $moviesResult->fetchArray(SQLITE3_ASSOC)) {
    $tmdbDetails = fetchTMDBDetails($row['api_id'], $row['type']);
    if ($tmdbDetails) {
        $movies[] = [
            'api_id' => $row['api_id'],
            'type' => $row['type'],
            'quality' => $row['quality'],
            'price' => $row['price'],
            'title' => $row['type'] === 'movie' ? ($tmdbDetails['title'] ?? '') : ($tmdbDetails['name'] ?? ''),
            'overview' => $tmdbDetails['overview'] ?? '',
            'poster_path' => $tmdbDetails['poster_path'] ?? null,
            'release_date' => $row['type'] === 'movie' ? 
                ($tmdbDetails['release_date'] ?? '') : 
                ($tmdbDetails['first_air_date'] ?? ''),
            'rating' => $tmdbDetails['vote_average'] ?? 0,
            'genres' => array_map(function($genre) {
                return $genre['name'];
            }, $tmdbDetails['genres'] ?? []),
            'status' => $tmdbDetails['status'] ?? '',
            'runtime' => $row['type'] === 'movie' ? 
                ($tmdbDetails['runtime'] ?? 0) : 
                ($tmdbDetails['episode_run_time'][0] ?? 0),
            'seasons' => $row['type'] === 'tv' ? 
                ($tmdbDetails['number_of_seasons'] ?? 0) : null,
            'episodes' => $row['type'] === 'tv' ? 
                ($tmdbDetails['number_of_episodes'] ?? 0) : null
        ];
    }
}

// Fetch software
$softwareStmt = $db->prepare("SELECT * FROM software WHERE user_id = :user_id ORDER BY timestamp DESC");
$softwareStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
$softwareResult = $softwareStmt->execute();
$software = [];
while ($row = $softwareResult->fetchArray(SQLITE3_ASSOC)) {
    $software[] = $row;
}

// Convert to JSON for JavaScript use
$moviesJson = json_encode($movies);
$softwareJson = json_encode($software);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Content - SeriesInTown</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <style>
        /* Previous styles remain unchanged */
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
            width: 100%;
            padding: 15px 20px;
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
        
        .content {
            margin-top: 50px;
            padding: 20px;
        }
        
        .searchbar {
            padding: 8px 10px;
            border-radius: 15px;
            border: 2px solid #888;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }
        
        .searchbar .input {
            font-size: 18px;
            width: 100%;
            border: none;
            outline: none;
            background-color: transparent;
        }
        
        .types {
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .types .type {
            padding: 7px 10px;
            border-radius: 20px;
            border: 1.5px solid #007aff;
            font-size: 14px;
            color: #007aff;
        }
        
        .types .active {
            color: #fff;
            border-color: #007aff;
            background-color: #007aff;
        }
        
        .content-container {
            margin-top: 20px;
        }
        
        .item-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .item-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .item-details {
            font-size: 14px;
            color: #666;
        }
        
        .no-content {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 16px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .item-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .item-content {
            padding: 0;
        }

        .item-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 8px;
            background: #007AFF;
            color: white;
        }

        .item-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .item-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-meta-details {
            font-size: 14px;
            color: #666;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }
        
        .item-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .item-meta-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 12px;
            font-size: 13px;
            color: #666;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-movie {
            background: #007AFF;
            color: white;
        }

        .badge-tv {
            background: #34C759;
            color: white;
        }

        /* Rest of your existing styles */
    </style>
</head>
<body>
    <!-- Header and navigation remain unchanged -->
    <div class="header">
            <h1>My Content</h1>
            <button onclick="showAddItem()" style="border: none; background: none; color: #007AFF; font-size: 15px; font-weight: 500;">
                Add Item
            </button>
        </div>
        
        <div class="content">
            <div class="searchbar">
                <input class="input" type="text" placeholder="Search your items" onkeyup="filterItems(this.value)">
                <span class="search">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368">
                        <path d="M320-516v-128q0-12 10.5-17.5t20.5.5l102 64q10 6 10 17t-10 17l-102 64q-10 6-20.5.5T320-516Zm60 196q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l224 224q11 11 11 28t-11 28q-11 11-28 11t-28-11L532-372q-30 24-69 38t-83 14Zm0-80q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                    </svg>
                </span>
            </div>
            
            <div class="types">
                <div class="type active" onclick="switchTab('movies')">Movies/Series</div>
                <div class="type" onclick="switchTab('games')">Games</div>
                <div class="type" onclick="switchTab('software')">Software</div>
            </div>
    
            <div class="content-container">
                <div id="movies-tab" class="tab-content active"></div>
                <div id="games-tab" class="tab-content"></div>
                <div id="software-tab" class="tab-content"></div>
            </div>
        </div>
    
    <script>
        const userData = {
            movies: <?php echo $moviesJson; ?>,
            software: <?php echo $softwareJson; ?>
        };

        function formatRuntime(minutes) {
            if (!minutes) return 'N/A';
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
        }

        function displayContent(tabName) {
            const contentContainer = document.getElementById(`${tabName}-tab`);
            let content = '';

            if (tabName === 'games') {
                content = '<div class="no-content">You have no games available</div>';
            } else {
                const items = userData[tabName] || [];
                
                if (items.length === 0) {
                    content = `<div class="no-content">You have no ${tabName} available</div>`;
                } else {
                    content = items.map(item => {
                        if (tabName === 'movies') {
                            const badgeClass = item.type === 'movie' ? 'badge-movie' : 'badge-tv';
                            const typeLabel = item.type === 'movie' ? 'Movie' : 'TV Series';
                            
                            return `
                                <div class="item-card">
                                    <img class="item-image" 
                                         src="${item.poster_path ? '<?php echo TMDB_IMG_BASE_URL; ?>' + item.poster_path : '/placeholder-image.jpg'}" 
                                         alt="${item.title}">
                                    <div class="item-content">
                                        <span class="badge ${badgeClass}">${typeLabel}</span>
                                        <div class="item-name">${item.title}</div>
                                        <div class="item-description">${item.overview}</div>
                                        
                                        <div class="item-meta-details">
                                            <div class="meta-item">
                                                <span>Quality:</span>
                                                <strong>${item.quality}</strong>
                                            </div>
                                            <div class="meta-item">
                                                <span>Price:</span>
                                                <strong>$${item.price}</strong>
                                            </div>
                                            ${item.type === 'movie' ? `
                                                <!-- <div class="meta-item">
                                                    <span>Runtime:</span>
                                                    <strong>${formatRuntime(item.runtime)}</strong>
                                                </div> -->
                                            ` : `
                                                <!-- <div class="meta-item">
                                                    <span>Seasons:</span>
                                                    <strong>${item.seasons}</strong>
                                                </div>
                                                <div class="meta-item">
                                                    <span>Episodes:</span>
                                                    <strong>${item.episodes}</strong>
                                                </div> -->
                                            `}
                                            <!-- <div class="meta-item">
                                                <span>Release:</span>
                                                <strong>${item.release_date.split('-')[0]}</strong>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            return `
                                <div class="item-card">
                                    <div class="item-content">
                                        <div class="item-name">${item.name}</div>
                                        <div class="item-meta">
                                            <div>Platform: ${item.platform}</div>
                                            <div>$${item.price}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }).join('');
                }
            }

            contentContainer.innerHTML = content;
        }
        
        // Initialize content
        document.addEventListener('DOMContentLoaded', function() {
            displayContent('movies');
        });

        function switchTab(tabName) {
            // Update tab styling
            document.querySelectorAll('.type').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Show selected content
            const selectedTab = document.getElementById(`${tabName}-tab`);
            selectedTab.classList.add('active');

            // Display content
            displayContent(tabName);
        }

        

        function filterItems(searchTerm) {
            const activeTab = document.querySelector('.tab-content.active').id.split('-')[0];
            const items = userData[activeTab] || [];
            
            const filteredItems = items.filter(item => 
                item.title.toLowerCase().includes(searchTerm.toLowerCase())
            );

            const contentContainer = document.getElementById(`${activeTab}-tab`);
            
            if (filteredItems.length === 0) {
                contentContainer.innerHTML = `<div class="no-content">No matching ${activeTab} found</div>`;
            } else {
                contentContainer.innerHTML = filteredItems.map(item => `
                    <div class="item-card">
                        <img class="item-image" 
                             src="${item.poster_path ? '<?php echo TMDB_IMG_BASE_URL; ?>' + item.poster_path : '/placeholder-image.jpg'}" 
                             alt="${item.title}">
                        <div class="item-content">
                            <span class="badge ${badgeClass}">${typeLabel}</span>
                            <div class="item-name">${item.title}</div>
                            <div class="item-description">${item.overview}</div>
                            
                            <div class="item-meta-details">
                                <div class="meta-item">
                                    <span>Quality:</span>
                                    <strong>${item.quality}</strong>
                                </div>
                                <div class="meta-item">
                                    <span>Price:</span>
                                    <strong>$${item.price}</strong>
                                </div>
                                ${item.type === 'movie' ? `
                                    <!-- <div class="meta-item">
                                        <span>Runtime:</span>
                                        <strong>${formatRuntime(item.runtime)}</strong>
                                    </div> -->
                                ` : `
                                    <!-- <div class="meta-item">
                                        <span>Seasons:</span>
                                        <strong>${item.seasons}</strong>
                                    </div>
                                    <div class="meta-item">
                                        <span>Episodes:</span>
                                        <strong>${item.episodes}</strong>
                                    </div> -->
                                `}
                                <!-- <div class="meta-item">
                                    <span>Release:</span>
                                    <strong>${item.release_date.split('-')[0]}</strong>
                                </div> -->
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        function showAddItem() {
            // Implement your add item functionality here
            window.location.href = "list.php";
        }

        // Rest of your JavaScript functions remain unchanged
    </script>
</body>
</html>