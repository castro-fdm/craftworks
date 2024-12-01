<?php
    session_start();
    include 'session_check.php';
    include 'db.php'; // Include database connection

    // Check if the user is logged in and has the admin role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: admin-login.php");
        exit("Access denied");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate input
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, trim($_POST['role']));

        // Validate required fields
        if (!$username || !$email || !$password || !$role) {
            die("All fields are required.");
        }

        // Insert data into the database
        $sql = "INSERT INTO users (username, email, password, role) 
                VALUES ('$username', '$email', '$password', '$role')";

        if (mysqli_query($conn, $sql)) {
            echo "User added successfully!";
            header("Location: admin-dashboard.php?category=Users");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
</head>
<body>
    <section id="navbar">
        <ul>
            <li><a href="admin-dashboard.php">Dashboard</a></li>
            <li><a href="logout.php" style="margin-right: 40px;">Logout</a></li>
        </ul>
    </section>
    <section id="main">
        <h2>Add New User</h2>
        <form action="add-user.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" required><br>

            <label for="role">Role:</label>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select><br>

            <button type="submit">Add User</button>
        </form>
    </section>
    <style>
        /* Same styles as in add-item.php */
        * {
            font-family: 'Roboto', sans-serif;
            margin: 0;
        }

        body {
            width: 100%;
            height: 100%;
            background-color: #efeaea;
        }

        section[id="navbar"] {
            display: flex;
            align-items: center;
            position: fixed;
            width: 100%;
            height: 50px;
            background-color: #333;
            z-index: 1;
        }

        section[id="navbar"] ul {
            list-style: none;
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin: 10px;
        }

        section[id="navbar"] a {
            color: white;
            text-decoration: none;
        }

        section[id="main"] {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 50px);
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 40%;
            margin: 20px auto;
        }

        form label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
        }

        form input, form select {
            width: 100%;
            height: 30px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
        }

        form button:hover {
            background-color: #45a049;
        }
    </style>
</body>
</html>
