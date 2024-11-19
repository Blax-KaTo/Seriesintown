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
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span class="text">Edit Profile</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 17H2a3 3 0 0 0-3 3v.5h26v-.5a3 3 0 0 0-3-3zm-12 .5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5V16h2v1.5z"></path>
                    <path d="M20 8h-3V6a5 5 0 0 0-10 0v2H4a2 2 0 0 0-2 2v7h20v-7a2 2 0 0 0-2-2zM9 6a3 3 0 1 1 6 0v2H9V6z"></path>
                </svg>
                <span class="text">Password & Security</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
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
            </div>
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
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span class="text">Help Center</span>
                <span class="arrow">›</span>
            </div>
            <div class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
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
            }, 200);
        });
    </script>
</body>
</html>