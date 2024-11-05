<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// register.php
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['usernameField'];
    $password = password_hash($_POST['passwordField'], PASSWORD_DEFAULT); // Hashing password for security

    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    
    try {
        if ($stmt->execute()) {
            header("Location: login.html");
        }
    } catch (mysqli_sql_exception $e) {
        header("Location: signup.html");
    }
}
?>
