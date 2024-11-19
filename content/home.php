
<?php

include ("auth_check.php");

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
            --card-width: min(280px, 68%);
            --card-gap: 40px;
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

        .app-container {
            width: 100%;
            height: var(--app-height);
            max-width: 430px;
            background: white;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 431px) {
            .app-container {
                border-radius: 30px;
                height: min(var(--app-height), 800px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: left;
            padding: env(safe-area-inset-top, 20px) 20px 15px;
            background: white;
            position: relative;
            z-index: 100;
            border-bottom: 1px solid #eee;
            flex-shrink: 0;
        }

        .logo {
            margin-top: 15px;
            font-size: clamp(20px, 5vw, 24px);
            font-weight: 700;
            color: #333;
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
            border-radius: 20px 20px 0 0;
            pointer-events: none;
        }

        .card-content {
            padding: 15px;
        }

        .title {
            font-size: clamp(18px, 4vw, 20px);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .price {
            font-size: clamp(16px, 3.5vw, 18px);
            color: #007AFF;
            font-weight: 500;
        }

        .nav-bar {
            padding: 15px 20px calc(env(safe-area-inset-bottom, 15px) + 15px);
            background: white;
            display: flex;
            justify-content: space-around;
            border-top: 1px solid #eee;
            flex-shrink: 0;
        }

        .nav-item {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease;
            font-size: 20px;
        }

        .nav-item:active {
            background: #e0e0e0;
        }
        
        .nav-item:active svg {
            fill: #fff !important;
        }

        .nav-item.active {
            background: #007AFF;
            color: white;
        }
        
        nav-item.active svg {
            fill: #fff !important;
        }

        @media (hover: hover) {
            .nav-item:hover {
                background: #e5e5e5;
            }
            
            .nav-item.active:hover {
                background: #0066d6;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="card-container" id="cardContainer">
            <!-- Cards will be inserted here by JavaScript -->
        </div>
    </div>

    <script src="script/script.js"></script>
    <script>
    
        // Handle mobile viewport height issues
        function setAppHeight() {
            document.documentElement.style.setProperty('--app-height', `${window.innerHeight}px`);
        }
        window.addEventListener('resize', setAppHeight);
        setAppHeight();

        const movies = [
            { title: 'Silicon Valley', price: '$4.99', image: '/api/placeholder/335/315' },
            { title: 'Tech Stars', price: '$5.99', image: '/api/placeholder/335/315' },
            { title: 'Startup Life', price: '$3.99', image: '/api/placeholder/335/315' },
            { title: 'Digital Dreams', price: '$6.99', image: '/api/placeholder/335/315' }
        ];

        let currentIndex = 0;
        const cardContainer = document.getElementById('cardContainer');

        function createCard(movie, index) {
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
                <img src="${movie.image}" alt="${movie.title}" draggable="false">
                <div class="card-content">
                    <div class="title">${movie.title}</div>
                    <div class="price">${movie.price}</div>
                </div>
            `;
            
            return card;
        }

        function renderCards() {
            cardContainer.innerHTML = '';
            movies.forEach((movie, index) => {
                if (Math.abs(index - currentIndex) <= 1) {
                    const card = createCard(movie, index);
                    cardContainer.appendChild(card);
                }
            });
        }

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