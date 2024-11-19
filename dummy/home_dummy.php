<?php
$api_key = 'ff3be76dc0cdcf90e42c31f9fcdd2cd8'; // Replace with your actual TMDB API key

// Function to fetch both movies and TV shows
function fetchContent($api_key) {
    $movies_url = "https://api.themoviedb.org/3/trending/movie/week?api_key={$api_key}";
    $tv_url = "https://api.themoviedb.org/3/trending/tv/week?api_key={$api_key}";
    
    $movies_json = file_get_contents($movies_url);
    $tv_json = file_get_contents($tv_url);
    
    $movies_data = json_decode($movies_json, true);
    $tv_data = json_decode($tv_json, true);
    
    $combined = array_merge(
        array_map(function($item) {
            return [
                'title' => $item['title'],
                'description' => $item['overview'],
                'image' => 'https://image.tmdb.org/t/p/w500' . $item['poster_path'],
                'type' => 'Movie'
            ];
        }, array_slice($movies_data['results'], 0, 5)),
        array_map(function($item) {
            return [
                'title' => $item['name'],
                'description' => $item['overview'],
                'image' => 'https://image.tmdb.org/t/p/w500' . $item['poster_path'],
                'type' => 'TV Series'
            ];
        }, array_slice($tv_data['results'], 0, 5))
    );
    
    shuffle($combined);
    return $combined;
}

$content = fetchContent($api_key);
$content_json = json_encode($content);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>SeriesinTown</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        :root {
            --app-height: 100vh;
            --card-width: min(400px, 78%);
            --card-gap: 50px;
            --transition-duration: 0.8s;
            --transition-timing: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        html, body {
            position: fixed;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }

        body {
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            overscroll-behavior: none;
        }

        .page-container {
            width: 100%;
            height: 100%;
            max-width: 430px;
            background: white;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 431px) {
            .app-container {
                height: min(var(--app-height), 800px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
        }

        .card-container {
            flex: 1;
            position: relative;
            overflow: hidden;
            touch-action: pan-x;
            perspective: 1000px;
        }

        .card {
            position: absolute;
            top: 50%;
            left: 50%;
            width: var(--card-width);
            aspect-ratio: 3/4;
            transform: translate(-50%, -50%) scale(0.85);
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform var(--transition-duration) var(--transition-timing);
            will-change: transform;
            touch-action: none;
            overflow: hidden;
        }

        .card.active {
            transform: translate(-50%, -50%) scale(1);
            z-index: 2;
        }

        .card.prev {
            transform: translate(calc(-50% - var(--card-width) - var(--card-gap)), -50%) scale(0.85);
            z-index: 1;
        }

        .card.next {
            transform: translate(calc(-50% + var(--card-width) + var(--card-gap)), -50%) scale(0.85);
            z-index: 1;
        }

        .card.dragging {
            transition: transform 0.1s linear;
        }

        .card img {
            width: 100%;
            height: 70%;
            object-fit: cover;
            pointer-events: none;
        }

        .card-content {
            padding: 15px;
            position: relative;
        }

        .title {
            font-size: clamp(18px, 4vw, 20px);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .description {
            font-size: 14px;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .content-type {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            z-index: 1;
        }

        .love-button {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #ff4757;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .love-button:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="card-container" id="cardContainer">
            <!-- Cards will be inserted here by JavaScript -->
        </div>
    </div>
    
    <script>
        // Handle mobile viewport height issues
        function setAppHeight() {
            document.documentElement.style.setProperty('--app-height', `${window.innerHeight}px`);
        }
        window.addEventListener('resize', setAppHeight);
        setAppHeight();

        const content = <?php echo $content_json; ?>;
        let currentIndex = 0;
        const cardContainer = document.getElementById('cardContainer');

        function createCard(item, index) {
            const card = document.createElement('div');
            card.className = 'card';
            
            if (index === currentIndex) {
                card.classList.add('active');
            } else if (index === currentIndex - 1) {
                card.classList.add('prev');
            } else if (index === currentIndex + 1) {
                card.classList.add('next');
            }
            
            card.innerHTML = `
                <div class="content-type">${item.type}</div>
                <img src="${item.image}" alt="${item.title}" draggable="false">
                <div class="card-content">
                    <div class="title">${item.title}</div>
                    <div class="description">${item.description}</div>
                    <div class="love-button">
                        <i class="fas fa-heart"></i>
                    </div>
                </div>
            `;
            
            return card;
        }

        function renderCards() {
            cardContainer.innerHTML = '';
            content.forEach((item, index) => {
                if (Math.abs(index - currentIndex) <= 1) {
                    const card = createCard(item, index);
                    cardContainer.appendChild(card);
                }
            });
        }

        // Rest of the JavaScript remains the same as in your original code
        // (touch and mouse event handlers)
        
        // Initial render
        
        // Initial render
        renderCards();
        
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        let startTime = 0;
        let cardWidth = 0;
        
        function handleStart(clientX) {
            if (isDragging) return;
            
            startX = clientX;
            currentX = clientX;
            isDragging = true;
            startTime = Date.now();
            cardWidth = document.querySelector('.card').offsetWidth;
            
            document.querySelectorAll('.card').forEach(card => {
                card.classList.add('dragging');
            });
        }
        
        function handleMove(clientX) {
            if (!isDragging) return;
            
            currentX = clientX;
            const diff = currentX - startX;
            const percentage = (diff / cardWidth) * 100;
            
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                if (card.classList.contains('active')) {
                    card.style.transform = `translate(-50%, -50%) scale(${Math.max(0.85, 1 - Math.abs(percentage) * 0.002)}) translateX(${percentage}%)`;
                } else if (card.classList.contains('prev')) {
                    const scale = Math.min(1, 0.85 + Math.max(0, percentage) * 0.002);
                    card.style.transform = `translate(calc(-50% - var(--card-width) - var(--card-gap) + ${percentage}%), -50%) scale(${scale})`;
                } else if (card.classList.contains('next')) {
                    const scale = Math.min(1, 0.85 + Math.max(0, -percentage) * 0.002);
                    card.style.transform = `translate(calc(-50% + var(--card-width) + var(--card-gap) + ${percentage}%), -50%) scale(${scale})`;
                }
            });
        }
        
        function handleEnd() {
            if (!isDragging) return;
            
            const diff = currentX - startX;
            const duration = Date.now() - startTime;
            const velocity = Math.abs(diff / duration);
            const threshold = cardWidth * 0.25; // Made threshold more sensitive
            
            let shouldChange = Math.abs(diff) > threshold || velocity > 0.3; // Made velocity threshold more sensitive
            
            if (shouldChange) {
                if (diff > 0 && currentIndex > 0) {
                    currentIndex--;
                } else if (diff < 0 && currentIndex < movies.length - 1) {
                    currentIndex++;
                }
            }
            
            document.querySelectorAll('.card').forEach(card => {
                card.style.transform = '';
                card.classList.remove('dragging');
            });
            
            renderCards();
            isDragging = false;
        }
        
        // Touch events
        cardContainer.addEventListener('touchstart', (e) => {
            handleStart(e.touches[0].clientX);
        }, { passive: true });
        
        cardContainer.addEventListener('touchmove', (e) => {
            handleMove(e.touches[0].clientX);
        }, { passive: true });
        
        cardContainer.addEventListener('touchend', () => {
            handleEnd();
        });
        
        // Mouse events
        cardContainer.addEventListener('mousedown', (e) => {
            handleStart(e.clientX);
        });
        
        cardContainer.addEventListener('mousemove', (e) => {
            handleMove(e.clientX);
        });
        
        cardContainer.addEventListener('mouseup', () => {
            handleEnd();
        });
        
        cardContainer.addEventListener('mouseleave', () => {
            if (isDragging) {
                handleEnd();
            }
        });
        
    </script>
</body>
</html>