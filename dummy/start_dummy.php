<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SpotsInTown</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <style>
        /* Your existing styles here */

        /* Loading screen styles */
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');

        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--background-gradient);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .typing-text {
            font-family: 'Courier Prime', monospace;
            font-size: 1rem;
            color: var(--primary-color);
            position: relative;
            white-space: nowrap;
            text-shadow: 0 0 10px rgba(0, 122, 255, 0.5);
            animation: glow 1.5s ease-in-out infinite alternate;
        }

        .typing-text::after {
            content: '|';
            position: absolute;
            right: -8px;
            animation: blink 0.75s step-end infinite;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 5px var(--primary-color),
                           0 0 10px var(--primary-color),
                           0 0 15px var(--primary-color);
            }
            to {
                text-shadow: 0 0 10px var(--primary-color),
                           0 0 20px var(--primary-color),
                           0 0 30px var(--primary-color);
            }
        }

        @keyframes blink {
            50% { opacity: 0; }
        }

        /* Hide main content initially */
        .main-content {
            opacity: 0;
            transition: opacity 1s ease-in;
        }

        .main-content.visible {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="typing-text"></div>
    </div>

    <!-- Main Content (your existing content) -->
    <div class="main-content">
        <!-- Your existing content here -->
        <div class="background-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <div class="splash-container">
            <!-- Rest of your existing content -->
        </div>
    </div>

    <script>
        // Typing animation function
        function typeWriter(text, element, delay = 100, breathingPoints = []) {
            let index = 0;
            
            function type() {
                if (index < text.length) {
                    element.textContent = text.substring(0, index + 1);
                    
                    // Check if current position is a breathing point
                    const currentDelay = breathingPoints.includes(index) ? delay * 5 : delay;
                    
                    index++;
                    setTimeout(type, currentDelay);
                } else {
                    // After typing is complete, wait and then fade out
                    setTimeout(() => {
                        const loadingScreen = document.querySelector('.loading-screen');
                        const mainContent = document.querySelector('.main-content');
                        
                        loadingScreen.style.opacity = '0';
                        mainContent.classList.add('visible');
                        
                        setTimeout(() => {
                            loadingScreen.style.display = 'none';
                        }, 500);
                    }, 1000);
                }
            }
            
            type();
        }

        // Start the typing animation when the page loads
        window.addEventListener('load', () => {
            const typingElement = document.querySelector('.typing-text');
            const text = "Inspired Us...";
            // Add breathing points after certain characters (indexes)
            const breathingPoints = [7, 11]; // Pause after "Inspired" and "Us"
            typeWriter(text, typingElement, 150, breathingPoints);
        });

        // Your existing scripts here
    </script>
</body>
</html>