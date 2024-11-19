<?php
// login_process.php

require 'config/db.php'; // Include the database connection file
session_start(); // Start session management

$errors = [];
$response = ["success" => false, "errors" => &$errors];

// Collect POST data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Input validation
if (empty($email)) {
    $errors['email'] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
}

if (empty($password)) {
    $errors['password'] = "Password is required.";
}

if (empty($errors)) {
    // Check for the user's email
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Set session variables and indicate login success
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;

            // Set a cookie that lasts for 30 days (60 * 60 * 24 * 30 seconds)
            $cookie_expiration = time() + (60 * 60 * 24 * 30);
            setcookie("user_id", $row['id'], $cookie_expiration, "/", "", true, true);
            setcookie("email", $email, $cookie_expiration, "/", "", true, true);

            $response["success"] = true;
        } else {
            $errors['password'] = "Incorrect password.";
        }
    } else {
        $errors['email'] = "No account found with this email.";
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
