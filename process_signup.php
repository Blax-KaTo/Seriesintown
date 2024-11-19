<?php
// process_signup.php

require 'config/db.php'; // Include the database connection file

// Function to create the users table if it doesn't exist
function createUsersTable($db) {
    $createTableSql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstname TEXT NOT NULL,
        lastname TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        gender TEXT NOT NULL,
        country TEXT NOT NULL,
        password TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    );";
    $db->exec($createTableSql);
}

// Start error handling and table creation
$db = getDatabaseConnection();
createUsersTable($db);

$errors = [];
$response = ["success" => false, "errors" => &$errors];

// Collect POST data
$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$gender = $_POST['gender'] ?? '';
$country = $_POST['country'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Input validation
if (empty($firstname)) $errors['firstname'] = "First name is required.";
if (empty($lastname)) $errors['lastname'] = "Last name is required.";
if (empty($email)) $errors['email'] = "Email is required.";
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";

if (empty($gender)) $errors['gender'] = "Gender is required.";
if (empty($country)) $errors['country'] = "Country is required.";

if (empty($password)) $errors['password'] = "Password is required.";
elseif ($password !== $confirmPassword) $errors['confirmPassword'] = "Passwords do not match.";

if (empty($errors)) {
    // Check for existing email
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_NUM)[0];

    if ($result > 0) {
        $errors['email'] = "This email is already registered.";
    } else {
        // Hash password and insert new user
        $stmt = $db->prepare("INSERT INTO users (firstname, lastname, email, gender, country, password) VALUES (:firstname, :lastname, :email, :gender, :country, :password)");
        $stmt->bindValue(':firstname', $firstname);
        $stmt->bindValue(':lastname', $lastname);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':gender', $gender);
        $stmt->bindValue(':country', $country);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT));

        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            $errors['general'] = "Error creating account.";
        }
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
