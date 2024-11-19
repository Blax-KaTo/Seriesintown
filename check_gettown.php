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
    // nothing here...
} else {
    header("Location: index.php");
}
?>
