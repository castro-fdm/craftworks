<?php
session_start();
include 'db.php'; // Include database connection

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit("Access denied");
}

// Check if the user ID is set in the query parameter
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch the user data from the database
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
} else {
    echo "No user found!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // If password is provided, hash it before saving
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the new password
        $update_sql = "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $username, $email, $role, $hashedPassword, $user_id);
    } else {
        // If no password is provided, just update the other fields
        $update_sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    }

    if ($stmt->execute()) {
        echo "User updated successfully!";
        header("Location: admin-dashboard.php");
        exit;
    } else {
        echo "Failed to update user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <title>Edit User</title>
</head>
<body>
    <section id="navbar">
        <ul>
            <li><a href="admin.html">Admin</a></li>
            <li><a href="index.html" style="margin-right: 40px;">Logout</a></li>
        </ul>
    </section>
    <section id="main">
        <h2>Edit User: <?php echo $user['username']; ?></h2>
        <form action="edit-users.php?id=<?php echo $user['id']; ?>" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" required><br>
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required><br>
            <label for="role">Role:</label>
            <select name="role">
                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
            </select><br>
            <label for="password">New Password:</label>
            <input type="password" name="password" placeholder="Leave empty if you don't want to change the password"><br>
            <button type="submit">Update User</button>
        </form>
    </section>
    <style>
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

        #main {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }


        /* Edit User Form Styles */
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
