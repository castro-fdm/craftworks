<?php
    session_start();
    require 'db.php'; // Include the database connection script

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php'); // Redirect to login page if not logged in
        exit();
    }

    // Fetch user data from the database
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, username, email, phone_number, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }

    // Update user profile data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_username = $_POST['username'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $phone_number = $_POST['phone_number'];

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password) || empty($phone_number)) {
            $error_message = "All fields are required.";
        } elseif (!password_verify($current_password, $user['password'])) {
            // Check if the current password is correct
            $error_message = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            // Check if the new password and confirm password match
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            // Password strength validation (minimum length)
            $error_message = "Password must be at least 6 characters long.";
        } elseif (!empty($new_username)) {
            // Check if the new username already exists
            $check_username_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_username_stmt->bind_param("s", $new_username);
            $check_username_stmt->execute();
            $username_result = $check_username_stmt->get_result();

            if ($username_result->num_rows > 0) {
                $error_message = "Username already taken. Please choose another one.";
            } else {
                // Proceed with the username change
                $update_username_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $update_username_stmt->bind_param("si", $new_username, $user_id);
                $update_username_stmt->execute();

                // Update session with the new username
                $_SESSION['username'] = $new_username;
            }
        }

        // If password is being updated, proceed with password update as well
        if (empty($error_message) && !empty($new_password)) {
            // Hash the new password and update it in the database
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update phone number and password in the database
            $update_stmt = $conn->prepare("UPDATE users SET phone_number = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $phone_number, $hashed_new_password, $user_id);

            if ($update_stmt->execute()) {
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Failed to update profile.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="/css/profile.css">
</head>
<body>
    <section id="navbarSection">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="#">About Us</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <section id="profileSection">
        <div class="profileContainer">
            <h1>Your Profile</h1>

            <!-- Display success or error message -->
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Profile Form -->
            <form action="profile.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" required>

                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" required>

                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" required>

                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Update Profile</button>
            </form>

            <a href="index.php">Back to Home</a>
        </div>
    </section>
    <style>
        /* General Layout */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        #navbarSection {
            display: flex;
            flex-direction: row;
            background-color: #f2f2f2;
            padding: 10px;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        #logo-link {
            text-decoration: none;
            color: #f2f2f2;   
        }

        #logo {
            width: 100%;
            height: 100%;
            margin: auto;
        }

        .logoContainer {
            display: flex;
            align-items: center;
            width: 200px;
            height: 50px;
        }

        nav {
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin-right: 20px;
        }
        nav ul li a {
            text-decoration: none;
            color: #000000;
            position: relative;
            font-weight: bold;
        }

        /* Profile Section */
        #profileSection {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .profileContainer {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        /* Form Elements */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        label {
            font-size: 1rem;
            font-weight: bold;
            text-align: left;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            width: 100%;
        }

        input[type="text"]:disabled {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        button {
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }

        /* Success/Error Messages */
        .success-message {
            color: green;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</body>
</html>
