<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>SeriesinTown</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <style>
        .listing-container {
            padding: 20px;
            height: 100%;
            overflow-y: auto;
            background: #f5f5f7;
            max-width: 800px;
            margin: 0 auto;
        }

        .section-card {
            background: white;
            border-radius: 18px;
            margin-bottom: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .source-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .source-button {
            padding: 15px;
            border: 2px solid #007AFF;
            border-radius: 12px;
            background: white;
            color: #007AFF;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .source-button.active {
            background: #007AFF;
            color: white;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .file-input {
            display: none;
        }

        .movie-results {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }

        .movie-card {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .movie-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .movie-poster {
            width: 100px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
        }

        .movie-info {
            flex: 1;
        }

        .movie-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #1d1d1f;
        }

        .movie-details {
            font-size: 14px;
            color: #86868b;
            margin: 0 0 12px 0;
        }

        .movie-description {
            font-size: 14px;
            color: #1d1d1f;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .trailer-container {
            margin-top: 15px;
        }

        .action-button {
            background: #007AFF;
            color: white;
            border: none;
            width: 100%;
            padding: 16px;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 20px;
        }

        .action-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        #loadingIndicator {
            text-align: center;
            padding: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="listing-container">
        <div class="section-card">
            <h1 class="section-title">Create New Listing</h1>
            
            <div class="source-buttons">
                <button class="source-button" data-source="local">Choose from device</button>
                <button class="source-button" data-source="search">Search movie</button>
            </div>

            <div class="search-container" style="display: none;">
                <input type="text" class="search-input" placeholder="Search for a movie..." id="searchInput">
                <input type="file" class="file-input" id="fileInput" accept="video/*">
            </div>

            <div id="loadingIndicator">Searching...</div>

            <div class="movie-results" id="movieResults"></div>

            <button class="action-button" id="continueButton" disabled>Continue to Next Step</button>
        </div>
    </div>

    <script>
        const API_KEY = 'ff3be76dc0cdcf90e42c31f9fcdd2cd8'; // Replace with your TMDB API key
        const sourceButtons = document.querySelectorAll('.source-button');
        const searchContainer = document.querySelector('.search-container');
        const searchInput = document.getElementById('searchInput');
        const fileInput = document.getElementById('fileInput');
        const movieResults = document.getElementById('movieResults');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const continueButton = document.getElementById('continueButton');
        let selectedMovie = null;

        // Source button handling
        sourceButtons.forEach(button => {
            button.addEventListener('click', () => {
                sourceButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                searchContainer.style.display = 'block';
                
                if (button.dataset.source === 'local') {
                    searchInput.style.display = 'none';
                    fileInput.style.display = 'block';
                    fileInput.click();
                } else {
                    searchInput.style.display = 'block';
                    fileInput.style.display = 'none';
                }
            });
        });

        // Search input handling
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchMovies(e.target.value);
            }, 500);
        });

        // File input handling
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Extract potential movie name from filename
                const fileName = file.name.replace(/\.[^/.]+$/, "")
                    .replace(/\./g, " ")
                    .replace(/(\d{4})|(\(.*\))|(\[.*\])/g, "")
                    .trim();
                searchMovies(fileName);
            }
        });

        async function searchMovies(query) {
            if (!query) return;
            
            movieResults.innerHTML = '';
            loadingIndicator.style.display = 'block';

            try {
                const response = await fetch(`https://api.themoviedb.org/3/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                loadingIndicator.style.display = 'none';
                
                data.results.slice(0, 5).forEach(movie => {
                    const movieCard = createMovieCard(movie);
                    movieResults.appendChild(movieCard);
                });
            } catch (error) {
                console.error('Error searching movies:', error);
                loadingIndicator.style.display = 'none';
                movieResults.innerHTML = '<p>Error searching for movies. Please try again.</p>';
            }
        }

        function createMovieCard(movie) {
            const card = document.createElement('div');
            card.className = 'movie-card';
            card.innerHTML = `
                <img class="movie-poster" 
                     src="${movie.poster_path ? 
                         `https://image.tmdb.org/t/p/w200${movie.poster_path}` : 
                         '/api/placeholder/200/300'}" 
                     alt="${movie.title}">
                <div class="movie-info">
                    <h3 class="movie-title">${movie.title}</h3>
                    <p class="movie-details">Released: ${movie.release_date?.split('-')[0] || 'N/A'}</p>
                    <p class="movie-description">${movie.overview || 'No description available.'}</p>
                    <div class="trailer-container" id="trailer-${movie.id}"></div>
                </div>
            `;

            card.addEventListener('click', () => {
                document.querySelectorAll('.movie-card').forEach(c => 
                    c.style.border = '1px solid #ddd');
                card.style.border = '2px solid #007AFF';
                selectedMovie = movie;
                continueButton.disabled = false;
                fetchTrailer(movie.id);
            });

            return card;
        }

        async function fetchTrailer(movieId) {
            try {
                const response = await fetch(`https://api.themoviedb.org/3/movie/${movieId}/videos?api_key=${API_KEY}`);
                const data = await response.json();
                const trailer = data.results.find(video => 
                    video.type === 'Trailer' && video.site === 'YouTube');
                
                if (trailer) {
                    const trailerContainer = document.getElementById(`trailer-${movieId}`);
                    trailerContainer.innerHTML = `
                        <iframe width="100%" 
                                height="200" 
                                src="https://www.youtube.com/embed/${trailer.key}" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                    `;
                }
            } catch (error) {
                console.error('Error fetching trailer:', error);
            }
        }

        continueButton.addEventListener('click', () => {
            if (selectedMovie) {
                // Store selected movie data and proceed to next page
                localStorage.setItem('selectedMovie', JSON.stringify(selectedMovie));
                window.location.href = 'listing-details.php'; // Navigate to next page
            }
        });
    </script>
</body>
</html>