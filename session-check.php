<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Check if the user has been inactive for too long
    if (time() - $_SESSION['last_activity'] > $_SESSION['timeout']) {
        // Unset and destroy the session
        session_unset();
        session_destroy();

        // Redirect to the login page with a message
        header("Location: login.php?message=Session expired. Please log in again.");
        exit();
    } else {
        // Update the last activity timestamp
        $_SESSION['last_activity'] = time();
    }
} else {
    // If no user session exists, redirect to the login page
    header("Location: login.php");
    exit();
}
?>