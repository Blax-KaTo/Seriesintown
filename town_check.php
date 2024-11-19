<?php
// town_check.php

// Include the database connection
require_once 'config/db.php';
//session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>console.error('User not logged in');</script>";
    return;
}

$user_id = $_SESSION['user_id'];

// Establish a database connection
$db = getDatabaseConnection();

// Prepare a statement to check for the user's town
$stmt = $db->prepare("SELECT town FROM town WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();

// Check if town data is missing
if (!$result->fetchArray(SQLITE3_ASSOC)) {
    // If no town, output the modal HTML and JavaScript to show the popup
    echo <<<HTML
    <div id="townModal" style="display: none;">
        <div class="modal-content">
            <h2>We need your town</h2>
            <p>To show you movies within your town, please select your current location.</p>
            <button onclick="window.location.href='gettown.php'">Get town</button>
        </div>
    </div>

    <script>
        // Show the town modal on page load if no town data is found
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("townModal");
            modal.style.display = "block";
        });
    </script>

    <style>
        /* Basic styling for modal popup */
        #townModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .modal-content button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }
    </style>
    HTML;
}
?>
