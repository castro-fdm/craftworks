<?php
    session_start();
    include 'db.php'; // Include the database connection

    // Check if the user is an admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

    // Check if an ID is provided in the URL
    if (isset($_GET['id'])) {
        $review_id = intval($_GET['id']); // Get the review ID from the URL

        // Delete the review from the database
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        // Bind the review ID to the statement and execute it
        $stmt->bind_param("i", $review_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Review deleted successfully!";
            $category = $_POST['category'] ?? 'Reviews'; // Get category from request
            header("Location: admin-dashboard.php?category=$category");
            exit;
        } else {
            $_SESSION['error_message'] = "Error: Unable to delete the review. Please try again later.";
        }

        // Redirect back to the admin dashboard or reviews page
        header("Location: admin-dashboard.php");
    } else {
        $_SESSION['error_message'] = "No review ID provided.";
        header("Location: admin-dashboard.php");
    }

    $stmt->close();
    $conn->close();
?>
