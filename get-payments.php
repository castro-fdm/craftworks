<?php
    session_start();
    include 'db.php';

    if (!isset($_SESSION['user_id'])) {
        die("Access denied");
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, total_amount, payment_date, payment_status 
            FROM payments 
            WHERE user_id = ? 
            ORDER BY payment_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<p>Payment ID: {$row['id']}, Amount: {$row['total_amount']}, Date: {$row['payment_date']}, Status: {$row['payment_status']}</p>";
    }
?>
