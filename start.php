<?php

include("loggedin_check.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SeriesInTown</title>
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
            --primary-color: #007AFF;
            --secondary-color: #5856d6;
            --text-color: #1d1d1f;
            --background-gradient: linear-gradient(135deg, #f5f5f7 0%, #ffffff 100%);
        }

        body {
            background: var(--background-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .splash-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            animation: fadeIn 1s ease forwards;
            position: relative;
            z-index: 1;
        }

        .logo-container {
            margin-bottom: 40px;
            perspective: 1000px;
        }

        .logo {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0, 122, 255, 0.2);
            animation: scaleIn 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            transform-style: preserve-3d;
        }

        .logo-icon {
            font-size: 50px;
            color: white;
            transform: translateY(40px);
            opacity: 0;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards 0.3s;
        }

        .title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 12px;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards 0.5s;
        }

        .subtitle {
            font-size: 17px;
            color: #666;
            margin-bottom: 40px;
            max-width: 280px;
            line-height: 1.4;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards 0.7s;
        }

        .button-container {
            width: 100%;
            max-width: 280px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transform: translateY(20px);
            opacity: 0;
            z-index:9000;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards 0.9s;
        }

        .primary-button {
            width: 100%;
            padding: 16px;
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .primary-button:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .primary-button:active:after {
            opacity: 1;
        }

        .primary-button:active {
            transform: scale(0.98);
        }

        .secondary-button {
            width: 100%;
            padding: 16px;
            background: rgba(0, 122, 255, 0.1);
            border: none;
            border-radius: 12px;
            color: var(--primary-color);
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .secondary-button:active {
            background: rgba(0, 122, 255, 0.15);
            transform: scale(0.98);
        }

        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            opacity: 0.1;
            animation: float 20s infinite;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: -5s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
            animation-delay: -10s;
        }

        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: -75px;
            animation-delay: -15s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0.8) rotateY(-20deg); }
            to { transform: scale(1) rotateY(0); }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(100px, 100px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #ffffff;
                --background-gradient: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            }

            .subtitle {
                color: #999;
            }

            .secondary-button {
                background: rgba(255, 255, 255, 0.1);
            }

            .shape {
                opacity: 0.05;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="splash-container">
        <div class="logo-container">
            <div class="logo">
                <div class="logo-icon">🎬</div>
            </div>
        </div>
        
        <h1 class="title">SeriesInTown.</h1>
        <p class="subtitle">Discover and share the latest movies playing in your town.</p>
        
        <div class="button-container">
            <a href="login.php" class="secondary-button">
                Log in.
            </a>
            <a href="signup.php" class="primary-button">
                Get Started
            </a>
        </div>
    </div>

    <script>

        // Add smooth orientation changes
        window.addEventListener('orientationchange', () => {
            document.documentElement.style.setProperty('--vh', ${window.innerHeight * 0.01}px);
        });

        // Add touch feedback for buttons
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('touchstart', (e) => {
                e.preventDefault();
            });
        });

        // Add 3D tilt effect to logo
        const logo = document.querySelector('.logo');
        document.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            logo.style.transform = rotateY(${xAxis}deg) rotateX(${yAxis}deg);
        });

        // Reset logo position when mouse leaves
        document.addEventListener('mouseleave', () => {
            logo.style.transform = 'rotateY(0) rotateX(0)';
        });
    </script>
</body>
</html>