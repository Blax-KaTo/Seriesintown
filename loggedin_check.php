<?php
//session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Check if the login cookies exist
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['email'])) {
        // Set session variables based on cookies
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['email'] = $_COOKIE['email'];
        $_SESSION['logged_in'] = true;
        // If logged in, redirect to index.php
        header("Location: index.php");
    } else {
        // If not logged in, do nothing
    }
}
?>
