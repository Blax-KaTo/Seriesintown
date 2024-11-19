<?php
session_start();

// Clear session variables
$_SESSION = [];
session_unset();
session_destroy();

// Expire the cookies
setcookie("user_id", "", time() - 3600, "/");
setcookie("email", "", time() - 3600, "/");

// Redirect to loading page
header("Location: index.php");
exit;
?>
