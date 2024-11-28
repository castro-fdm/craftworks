<?php
    session_start();
    require 'db.php'; // Include your database connection script

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get user inputs from the form
        $username = $_POST['usernameField'];
        $email = $_POST['emailField'];
        $password = $_POST['passwordField'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            $query_params = http_build_query([
                'usernameError' => 'Username is required.',
                'emailError' => 'Email is required.',
                'passwordError' => 'Password is required.',
            ]);
            header("Location: login.php?$query_params");
            exit();
        }

        // Check user credentials in the database
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                $_SESSION['timeout'] = 600;

                // Redirect to index.php
                header("Location: index.php");
                exit();
            } else {
                $query_params = http_build_query([
                    'passwordError' => 'Incorrect password.',
                ]);
                header("Location: login.php?$query_params");
                exit();
            }
        } else {
            $query_params = http_build_query([
                'usernameError' => 'Invalid username or email.',
                'emailError' => 'Please check your email.',
            ]);
            header("Location: login.php?$query_params");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/login.css">
    <title>Login</title>
</head>
<body>
    <section id="navBar">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </section>
    <section name="login">
        <div class="loginContainer">
            <!-- Show logout success message if query parameter is present -->
            <?php if (isset($_GET['message']) && $_GET['message'] === 'logout_success'): ?>
                <div class="success-message">
                    You have successfully logged out!
                </div>
                <script>
                    // Automatically hide the message after a few seconds
                    setTimeout(() => {
                        document.querySelector('.success-message').style.display = 'none';
                    }, 3000); // 3 seconds
                </script>
            <?php endif; ?>

            <h1>Login</h1>
            <form name="login_form" action="login.php" method="POST">
                <label for="usernameField">Username:</label>
                <input name="usernameField" type="text" required>
                <span class="error-message" id="usernameError"></span>

                <label for="emailField">Email:</label>
                <input name="emailField" type="email" required>
                <span class="error-message" id="emailError"></span>

                <label for="passwordField">Password:</label>
                <input name="passwordField" type="password" required>
                <span class="error-message" id="passwordError"></span>

                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="signup.html">Sign Up</a></p> 
        </div>  
    </section>
    <script>
        // Display errors from query parameters
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const usernameError = urlParams.get('usernameError');
            const emailError = urlParams.get('emailError');
            const passwordError = urlParams.get('passwordError');
    
            if (usernameError) document.getElementById('usernameError').textContent = usernameError;
            if (emailError) document.getElementById('emailError').textContent = emailError;
            if (passwordError) document.getElementById('passwordError').textContent = passwordError;
        };
    </script>
</body>
</html>
