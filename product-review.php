<?php
    session_start();
    include 'db.php';

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $order_id = intval($_POST['order_id']);
        $user_id = intval($_POST['user_id']);
        $rating = intval($_POST['rating']);
        $review = trim($_POST['review']);

        // Validate inputs
        if ($rating < 1 || $rating > 5) {
            echo "Invalid rating. Please select a rating between 1 and 5.";
            exit();
        }

        if (empty($review)) {
            echo "Review cannot be empty. Please write a review.";
            exit();
        }

        // Insert review into the database
        $sql = "INSERT INTO reviews (order_id, user_id, review_text, rating, review_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("iisi", $order_id, $user_id, $review, $rating);

        if ($stmt->execute()) {

            header("Location: order.php");
        } else {
            // Provide detailed error message
            echo "Error: Unable to submit review. Please try again later.";
        }

        $stmt->close();
        $conn->close();
    } else {
        header("Location: order.php");
        exit();
    }
?>
