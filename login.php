<?php

include("loggedin_check.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <link rel="stylesheet" href="style/login.css">
    <style>
        /* Styling similar to signup form */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8em;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }
        label {
            font-size: 1em;
            font-weight: bold;
            color: #555;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }
        .error-message {
            color: #d9534f;
            font-size: 0.85em;
        }
        input[type="submit"] {
            padding: 12px;
            font-size: 1em;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("loginForm");
            form.addEventListener("submit", function (e) {
                e.preventDefault();

                // Clear previous error messages
                document.querySelectorAll(".error-message").forEach(e => e.textContent = "");

                const formData = new FormData(form);

                fetch("login_process.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Login successful!");
                        window.location.href = "index.php"; // Redirect to home page or desired page
                    } else {
                        console.log("Errors received:", data.errors); // Log errors for debugging

                        for (const [field, message] of Object.entries(data.errors)) {
                            const errorDiv = document.getElementById(`${field}Error`);
                            if (errorDiv) {
                                errorDiv.textContent = message;
                            } else {
                                console.warn(`No error element found for field: ${field}`);
                            }
                        }
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Login Form</h2>
        <form id="loginForm">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <div class="error-message" id="emailError"></div>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <div class="error-message" id="passwordError"></div>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
