<?php
    session_start();
    include 'db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("Access denied");
    }

    $user_id = $_SESSION['user_id'];

    // Calculate total amount
    $sql = "SELECT c.product_id, c.quantity, i.price
            FROM cart c
            JOIN inventory i ON c.product_id = i.id
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_amount = 0;
    $cart_items = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] > $row['price']) {
            die("Insufficient inventory for product ID " . $row['product_id']);
        }
        $total_amount += $row['price'] * $row['quantity'];
        $cart_items[] = $row; // Store for inventory deduction
    }

    // Log payment
    $sql = "INSERT INTO payments (user_id, total_amount, payment_date, payment_status)
            VALUES (?, ?, NOW(), 'Completed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $total_amount);

    if ($stmt->execute()) {
        $payment_id = $stmt->insert_id;

        // Deduct inventory
        foreach ($cart_items as $item) {
            $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }

        // Clear cart
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        echo "Checkout successful! Payment logged.";
    } else {
        echo "Failed to process checkout!";
    }
?>
