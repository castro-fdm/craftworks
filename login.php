<?php
// login.php
include 'db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['usernameField'];
    $password = $_POST['passwordField'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            echo'Login successful! Redirecting...';
            header("Location: index.html");
            exit();
        } else {
            header("Location: login.html");
        }
    } else {
        echo "User not found!";
    }
}
?>
