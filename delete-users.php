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

    // Delete the user from the database
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully!";
        header("Location: admin-dashboard.php");
        exit;
    } else {
        echo "Failed to delete user.";
    }
} else {
    echo "No user found!";
}
?>
