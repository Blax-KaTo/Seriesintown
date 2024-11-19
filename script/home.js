
alert("mother")
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