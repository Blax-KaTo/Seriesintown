<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500&family=Montserrat:wght@500&family=Roboto+Mono&family=Playfair+Display&family=Poppins:wght@500&family=Inter:wght@500&family=Source+Code+Pro&family=Space+Grotesk:wght@500&family=JetBrains+Mono&family=Urbanist:wght@500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .loading-page {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .aurora-container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            opacity: 0.5;
        }

        .aurora {
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: 
                radial-gradient(circle at center,
                    transparent 0%,
                    rgba(0, 122, 255, 0.1) 20%,
                    rgba(88, 86, 214, 0.1) 40%,
                    rgba(94, 92, 230, 0.1) 60%,
                    rgba(0, 122, 255, 0.1) 80%,
                    transparent 100%);
            animation: aurora-shift 10s linear infinite;
            transform-origin: center center;
            opacity: 0;
        }

        .aurora::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at center,
                    transparent 0%,
                    rgba(255, 59, 48, 0.1) 30%,
                    rgba(88, 86, 214, 0.1) 50%,
                    rgba(0, 122, 255, 0.1) 70%,
                    transparent 100%);
            animation: aurora-pulse 6s ease infinite;
        }

        .typing-container {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            height: 40px;
        }

        .typing-text {
            font-size: 1.2rem;
            white-space: nowrap;
            display: flex;
            gap: 1px;
        }

        .letter {
            color: #007AFF;
            opacity: 0;
            transform: translateY(10px);
            text-shadow: 
                0 0 10px rgba(0, 122, 255, 0.5),
                0 0 20px rgba(0, 122, 255, 0.3);
            animation: text-glow 2s ease-in-out infinite alternate;
        }

        .letter.visible {
            animation: letter-appear 0.3s forwards ease-out;
        }

        .cursor {
            position: relative;
            width: 2px;
            height: 24px;
            background: #007AFF;
            margin-left: 2px;
            animation: cursor-blink 0.6s step-end infinite;
        }

        @keyframes aurora-shift {
            0% {
                transform: rotate(0deg) scale(1);
            }
            50% {
                transform: rotate(180deg) scale(1.2);
            }
            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        @keyframes aurora-pulse {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 1;
            }
        }

        @keyframes letter-appear {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes text-glow {
            from {
                text-shadow: 
                    0 0 10px rgba(0, 122, 255, 0.5),
                    0 0 20px rgba(0, 122, 255, 0.3);
            }
            to {
                text-shadow: 
                    0 0 15px rgba(0, 122, 255, 0.7),
                    0 0 30px rgba(0, 122, 255, 0.5);
            }
        }

        @keyframes cursor-blink {
            50% { opacity: 0; }
        }

        .fade-out {
            animation: fade-out 0.8s forwards;
        }

        @keyframes fade-out {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        .aurora.visible {
            animation: aurora-fade-in 1.5s forwards;
        }

        @keyframes aurora-fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="loading-page">
        <div class="aurora-container">
            <div class="aurora"></div>
        </div>
        <div class="typing-container">
            <div class="typing-text"></div>
            <div class="cursor"></div>
        </div>
    </div>

    <script>
        const fonts = [
            'SF Pro Display',
            'Montserrat',
            'Playfair Display',
            'Poppins',
            'Inter',
            'Source Code Pro',
            'Space Grotesk',
            'JetBrains Mono',
            'Urbanist',
            'Roboto Mono'
        ];

        function typeWriter(text, element, delay = 80) {
            const aurora = document.querySelector('.aurora');
            aurora.classList.add('visible');
            
            const letters = text.split('');
            element.innerHTML = letters.map(letter => 
                `<span class="letter" style="font-family: ${fonts[Math.floor(Math.random() * fonts.length)]}">
                    ${letter}
                </span>`
            ).join('');
            
            const letterElements = element.querySelectorAll('.letter');
            let index = 0;
            
            function revealLetter() {
                if (index < letterElements.length) {
                    letterElements[index].classList.add('visible');
                    index++;
                    setTimeout(revealLetter, delay);
                } else {
                    // After typing completes, wait 1.5 seconds and redirect
                    setTimeout(() => {
                        document.querySelector('.loading-page').classList.add('fade-out');
                        setTimeout(() => {
                            window.location.href = 'start.php'; // Replace with your main page
                        }, 800);
                    }, 1500);
                }
            }
            
            revealLetter();
        }

        // Start animation when page loads
        window.addEventListener('load', () => {
            const typingElement = document.querySelector('.typing-text');
            typeWriter("Inspired Us...", typingElement, 120);
        });
    </script>
</body>
</html>