<?php
// Start session and include SQLite3 database connection
//session_start();
require_once 'config/db.php'; // Update with your actual SQLite3 database configuration file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login"); // Redirect to login page if not logged in
    exit();
}

// Fetch user information from the database
$user_id = $_SESSION['user_id'];
$db = getDatabaseConnection(); // Replace 'your_database.db' with the actual path to your SQLite database

$query = "SELECT firstname, lastname, email FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();

// Fetch data
$user = $result->fetchArray(SQLITE3_ASSOC);
$first_name = htmlspecialchars($user['firstname']);
$last_name = htmlspecialchars($user['lastname']);
$email = htmlspecialchars($user['email']);

$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .profile-container {
            padding: 20px;
            height: 100%;
            overflow-y: auto;
            background: #f5f5f7;
        }

        .profile-header {
            background: white;
            border-radius: 18px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-name {
            flex: 1;
        }

        .profile-name h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .profile-name p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #86868b;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-number {
            font-size: 24px;
            font-weight: 600;
            color: #1d1d1f;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #86868b;
        }

        .section-card {
            background: white;
            border-radius: 18px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f7;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item:hover {
            background-color: #f5f5f7;
        }

        .menu-item .icon {
            width: 24px;
            height: 24px;
            margin-right: 15px;
            color: #007AFF;
        }
        
        .menu-item .google-icon {
            fill: #007AFF;
        }

        .menu-item .text {
            flex: 1;
            font-size: 16px;
            color: #1d1d1f;
        }

        .menu-item .value {
            font-size: 16px;
            color: #86868b;
        }

        .menu-item .arrow {
            color: #86868b;
            margin-left: 10px;
        }

        .section-title {
            padding: 20px 20px 0 20px;
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .logout-button {
            background: #ff3b30;
            color: white;
            border: none;
            width: 100%;
            padding: 16px;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .logout-button:hover {
            background: #ff453a;
        }

        .version-text {
            text-align: center;
            color: #86868b;
            font-size: 13px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-picture">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#86868b" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="profile-name">
                <h2><?php echo $first_name . ' ' . $last_name; ?></h2>
                <p><?php echo $email; ?></p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number">127</div>
                <div class="stat-label">Watched</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">45</div>
                <div class="stat-label">Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">12</div>
                <div class="stat-label">Lists</div>
            </div>
        </div>

        <!-- Account Section -->
        <div class="section-card">
            <h3 class="section-title">Account</h3>
            <div class="menu-item">
               <svg class="icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#007AFF"><path d="M440-440v120q0 17 11.5 28.5T480-280q17 0 28.5-11.5T520-320v-120h120q17 0 28.5-11.5T680-480q0-17-11.5-28.5T640-520H520v-120q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640v120H320q-17 0-28.5 11.5T280-480q0 17 11.5 28.5T320-440h120Zm40 360q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                <span class="text">Listings</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="text">Edit Profile</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#007AFF"><path d="M280-400q-33 0-56.5-23.5T200-480q0-33 23.5-56.5T280-560q33 0 56.5 23.5T360-480q0 33-23.5 56.5T280-400Zm0 160q-100 0-170-70T40-480q0-100 70-170t170-70q67 0 121.5 33t86.5 87h335q8 0 15.5 3t13.5 9l80 80q6 6 8.5 13t2.5 15q0 8-2.5 15t-8.5 13L805-325q-5 5-12 8t-14 4q-7 1-14-1t-13-7l-52-39-57 43q-5 4-11 6t-12 2q-6 0-12.5-2t-11.5-6l-61-43h-47q-32 54-86.5 87T280-240Zm0-80q56 0 98.5-34t56.5-86h125l58 41v.5-.5l82-61 71 55 75-75h-.5.5l-40-40v-.5.5H435q-14-52-56.5-86T280-640q-66 0-113 47t-47 113q0 66 47 113t113 47Z"/></svg>
                <span class="text">Password & Security</span>
                <span class="arrow">›</span>
            </div>
            <!-- <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    <path d="M18.63 13A17.89 17.89 0 0 1 18 8"></path>
                    <path d="M6.26 6.26A5.86 5.86 0 0 0 6 8c0 7-3 9-3 9h14"></path>
                    <path d="M18 8a6 6 0 0 0-9.33-5"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
                <span class="text">Notifications</span>
                <span class="value">Off</span>
                <span class="arrow">›</span>
            </div> -->
        </div>

        <!-- Settings Section -->
        <div class="section-card">
            <h3 class="section-title">Settings</h3>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span class="text">App Preferences</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span class="text">Language</span>
                <span class="value">English</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 6v6l4 2"></path>
                </svg>
                <span class="text">Time Zone</span>
                <span class="value">UTC-5</span>
                <span class="arrow">›</span>
            </div>
        </div>

        <!-- Help Section -->
        <div class="section-card">
            <h3 class="section-title">Help</h3>
            <div class="menu-item">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#007AFF"><path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm2 160q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Zm4-172q25 0 43.5 16t18.5 40q0 22-13.5 39T502-525q-23 20-40.5 44T444-427q0 14 10.5 23.5T479-394q15 0 25.5-10t13.5-25q4-21 18-37.5t30-31.5q23-22 39.5-48t16.5-58q0-51-41.5-83.5T484-720q-38 0-72.5 16T359-655q-7 12-4.5 25.5T368-609q14 8 29 5t25-17q11-15 27.5-23t34.5-8Z"/></svg>
                <span class="text">Help Center</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#007AFF"><path d="M480-280q17 0 28.5-11.5T520-320v-160q0-17-11.5-28.5T480-520q-17 0-28.5 11.5T440-480v160q0 17 11.5 28.5T480-280Zm0-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>
                <span class="text">About</span>
                <span class="arrow">›</span>
            </div>
        </div>

        <button class="logout-button">Sign Out</button>

        <div class="version-text">Version 1.0.0</div>
    </div>

    <script>
        // Add click effect to menu items
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', () => {
                item.style.backgroundColor = '#ebebeb';
                setTimeout(() => {
                    item.style.backgroundColor = '';
                }, 200);
            });
        });

        // Add click effect to logout button
        const logoutButton = document.querySelector('.logout-button');
        logoutButton.addEventListener('click', () => {
            logoutButton.style.backgroundColor = '#ff1f1f';
            setTimeout(() => {
                logoutButton.style.backgroundColor = '';
                window.location.href="logout.php";
            }, 200);
        });
    </script>
</body>
</html>