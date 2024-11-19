<?php

include("loggedin_check.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/eruda/3.0.1/eruda.min.js"></script>
    <script>eruda.init();</script>
    <link rel="stylesheet" href="style/signup.css">
    <style>
        /* Reset and basic styling */
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
        
        /* Main container */
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

        /* Form styling */
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

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        .error-message {
            color: #d9534f;
            font-size: 0.85em;
        }

        /* Submit button styling */
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
        
        .name-cont {
            display: flex;
            flex-direction: rows;
            justify-content: space-between;
            gap: .5em;
        }

        /* Modal styling */
        #successModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 300px;
        }

        .modal-content h3 {
            margin-bottom: 1rem;
            color: #28a745;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Load countries into the select dropdown
            async function loadCountries() {
                const countrySelect = document.getElementById('country');
                const response = await fetch('https://restcountries.com/v3.1/all');
                const countries = await response.json();

                const defaultOption = new Option("Select a country", "");
                const zambiaOption = new Option("Zambia", "Zambia");

                countrySelect.add(defaultOption);
                countrySelect.add(zambiaOption);

                countries.filter(c => c.name.common !== "Zambia")
                    .sort((a, b) => a.name.common.localeCompare(b.name.common))
                    .forEach(c => countrySelect.add(new Option(c.name.common, c.name.common)));
            }

            loadCountries();

            // Handle form submission with AJAX
            const form = document.getElementById("signupForm");
            form.addEventListener("submit", function (e) {
                e.preventDefault();

                // Clear previous error messages
                document.querySelectorAll(".error-message").forEach(e => e.textContent = "");

                const formData = new FormData(form);

                fetch("process_signup.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success modal if account created successfully
                        document.getElementById("successModal").style.display = "flex";
                    } else {
                        console.log("Errors received:", data.errors); // Log errors for debugging

                        // Loop through each error field and assign messages
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

            // Redirect to login.php on button click
            document.getElementById("loginBtn").addEventListener("click", function () {
                window.location.href = "login.php";
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Signup Form</h2>
        <form id="signupForm">
            <div class="name-cont">
                <label for="firstname">First Name:
                    <input type="text" name="firstname" id="firstname" required>
                    <div class="error-message" id="firstnameError"></div>
                </label>

                <label for="lastname">Last Name:
                    <input type="text" name="lastname" id="lastname" required>
                    <div class="error-message" id="lastnameError"></div>
                </label>
            </div>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <div class="error-message" id="emailError"></div>

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="">Select a gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <div class="error-message" id="genderError"></div>

            <label for="country">Country:</label>
            <select name="country" id="country" required>
                <option value="">Select a country</option>
            </select>
            <div class="error-message" id="countryError"></div>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <div class="error-message" id="passwordError"></div>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <div class="error-message" id="confirmPasswordError"></div>

            <input type="submit" value="Create Account">
        </form>
    </div>

    <!-- Success Modal -->
    <div id="successModal">
        <div class="modal-content">
            <h3>Account Created Successfully!</h3>
            <button id="loginBtn">Login</button>
        </div>
    </div>
</body>
</html>
