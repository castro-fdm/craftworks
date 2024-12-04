<?php
    session_start();
    include 'db.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['usernameField'];
        $email = $_POST['emailField'];
        $password = $_POST['passwordField'];

        // Initialize error variables
        $emailError = $usernameError = $passwordError = "";

        // Validate that all fields are provided
        if (empty($username)) {
            $usernameError = "Username is required.";
        }
        if (empty($email)) {
            $emailError = "Email is required.";
        }
        if (empty($password)) {
            $passwordError = "Password is required.";
        }

        // If there are validation errors, redirect back to login page with error messages
        if ($usernameError || $emailError || $passwordError) {
            header("Location: login.html?usernameError=$usernameError&emailError=$emailError&passwordError=$passwordError");
            exit();
        }

        // Query to check if the email and username combination exists
        $sql = "SELECT * FROM users WHERE username = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email; // Store email in session if needed
                echo 'Login successful! Redirecting...';
                header("Location: index.html");
                exit();
            } else {
                header("Location: login.html?passwordError=Incorrect%20password.");
                exit();
            }
        } else {
            header("Location: login.html?emailError=User%20not%20found.");
            exit();
        }
    }
?>
