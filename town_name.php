<?php
// Start session to access user_id
//session_start();
require_once 'config/db.php'; // Include database connection

include("auth_check.php");

// Initialize global variable for the town name
$townName = "";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Connect to the database
    $db = getDatabaseConnection();
    
    // Prepare and execute the SQL query to retrieve the town name
    $stmt = $db->prepare("SELECT town FROM town WHERE user_id = :user_id ORDER BY timestamp DESC LIMIT 1");
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    // Fetch the town name
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $townName = $row['town'];
    }
}

// Store the town name in a global variable for other scripts
$GLOBALS['townName'] = $townName;