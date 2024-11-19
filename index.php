<?php
// Include authentication check
include("auth_check.php");

// town check
include("town_check.php");

// town name
include("town_name.php");

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
        /* Your exact styles as provided */
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        
        :root { --app-height: 100vh; --card-width: min(280px, 68%); --card-gap: 40px; --transition-duration: 0.8s; --transition-timing: cubic-bezier(0.34, 1.56, 0.64, 1); }

        html, body { position: fixed; overflow: hidden; width: 100%; height: 100%; }
        body { background: #f0f0f0; display: flex; justify-content: center; align-items: center; overscroll-behavior: none; }

        .app-container { width: 100%; height: var(--app-height); max-width: 430px; background: white; position: relative; display: flex; flex-direction: column; }

        @media (min-width: 431px) {
            .app-container { border-radius: 30px; height: min(var(--app-height), 800px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        }

        .header { display: flex; align-items: center; justify-content: space-between; padding: env(safe-area-inset-top, 20px) 20px 15px; background: white; z-index: 100; border-bottom: 1px solid #eee; flex-shrink: 0; }

        .logo { margin-top: 15px; font-size: clamp(20px, 5vw, 24px); font-weight: 700; color: #333; }
        .town-name {
            padding: 5px 10px;
            margin-top: 15px;
            border-radius: 20px;
            background-color: #fff;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 5px;
            transition: 0.7s;
        }
        
        .town-name:hover {
            background-color: #eee;
        }
        
        .town-name .town-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            border-radius: 50%;
            background-color: #eee;
        }
        
        .town-name .town-icon svg {
            height: 18px;
            width: 18px;
        }

        /* Containers for each page */
        .content-container { display: none; flex: 1; position: relative; overflow-y: auto; padding: 0; }
        .content-container.active { display: block; }

        /* Nav bar styles */
        .nav-bar { padding: 15px 20px calc(env(safe-area-inset-bottom, 15px) + 15px); background: white; display: flex; justify-content: space-around; border-top: 1px solid #eee; flex-shrink: 0; }
        .nav-item { width: 44px; height: 44px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 20px; }
        .nav-item.active { background: #007AFF; color: white; }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <div class="logo"><p>SeriesinTown</p></div>
            <div class="town-name"><span><?php echo htmlspecialchars($GLOBALS["townName"]); ?></span><div class="town-icon"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M360-440h80v-110h80v110h80v-190l-120-80-120 80v190Zm120 254q122-112 181-203.5T720-552q0-109-69.5-178.5T480-800q-101 0-170.5 69.5T240-552q0 71 59 162.5T480-186Zm0 79q-14 0-28-5t-25-15q-65-60-115-117t-83.5-110.5q-33.5-53.5-51-103T160-552q0-150 96.5-239T480-880q127 0 223.5 89T800-552q0 45-17.5 94.5t-51 103Q698-301 648-244T533-127q-11 10-25 15t-28 5Zm0-453Z"/></svg></div></div>
        </div>

        <!-- Page containers with PHP includes -->
        <div class="content-container active" id="home">
            <?php include("home1.php"); ?>
        </div>
        <div class="content-container" id="search">
            <?php include("search.php"); ?>
        </div>
        <div class="content-container" id="feed">
            <?php include("feed.php"); ?>
        </div>
        <div class="content-container" id="favorites">
            <?php include("favorites.php"); ?>
        </div>
        <div class="content-container" id="profile">
            <?php include("profile.php"); ?>
        </div>

   <div class="nav-bar">
        <!-- Loading placeholders --> 
        <div class="nav-item loading" data-target="home"></div>
        <div class="nav-item loading" data-target="search"></div>
        <div class="nav-item loading" data-target="feed"></div>
        <div class="nav-item loading" data-target="favorites"></div>
        <div class="nav-item loading" data-target="profile"></div>
       </div>
   </div>
   
   <script src="script/script.js"></script>
   <script>
 function loadNavigation() {
     fetch('nav-icons.php', {
         headers: {
             'X-Requested-With': 'XMLHttpRequest'
         }
     })
     .then(response => {
         if (!response.ok) {
             throw new Error(`HTTP error! status: ${response.status}`);
         }
         console.log('Response:', response);
         return response.json();
     })
     .then(icons => {
         console.log('Parsed icons:', icons);
         
         const navBar = document.querySelector('.nav-bar');
         if (!navBar) {
             throw new Error('Navigation bar element not found');
         }
         navBar.innerHTML = '';
         
         icons.forEach((icon, index) => {
             const navItem = document.createElement('div');
             navItem.className = `nav-item${icon.active ? ' active' : ''}`;
             navItem.id = icon.id;
             navItem.setAttribute('data-target', icon.id); // Add data-target attribute
             navItem.innerHTML = icon.emoji;
             
             // Add staggered animation
             setTimeout(() => {
                 navBar.appendChild(navItem);
                 setTimeout(() => navItem.classList.add('loaded'), 50);
             }, index * 100);
             
             // Add click handler
             navItem.addEventListener('click', () => {
                 // Update active state on nav items
                 document.querySelectorAll('.nav-item').forEach(item => {
                     item.classList.remove('active');
                 });
                 navItem.classList.add('active');
                 
                 // Show the corresponding content container
                 document.querySelectorAll('.content-container').forEach(container => {
                     container.classList.remove('active');
                 });
                 const targetContainer = document.getElementById(icon.id);
                 if (targetContainer) {
                     targetContainer.classList.add('active');
                 }
                 
                 console.log(`Navigating to: ${icon.id}`);
             });
         });
     })
     .catch(error => {
         console.error('Error loading navigation:', error);
         const navBar = document.querySelector('.nav-bar');
         if (navBar) {
             navBar.innerHTML = '<div class="nav-item">⚠️</div>';
         }
     });
 }
 
 // Load navigation when the page loads
 document.addEventListener('DOMContentLoaded', () => {
     loadNavigation();
     
     // Set app height for mobile view adjustments
     function setAppHeight() {
         document.documentElement.style.setProperty('--app-height', `${window.innerHeight}px`);
     }
     
     window.addEventListener('resize', setAppHeight);
     setAppHeight();
 });
    </script>
</body>
</html>
