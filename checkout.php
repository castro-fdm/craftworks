<?php
    var_dump($_POST);
    session_start();
    include 'db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("Access denied");
    }

    $user_id = $_SESSION['user_id'];

    // Fetch the payment method and billing address from the form
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery'; // Default to 'Cash on Delivery' if not set
    // Sanitize billing address
    $billing_address = mysqli_real_escape_string($conn, $_POST['billing_address'] ?? ''); // Default to empty if not set

    // Calculate total amount
    $sql = "SELECT c.product_id, c.quantity, i.price, i.product_name, i.quantity AS stock_quantity
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
        if ($row['quantity'] > $row['stock_quantity']) {
            die("Insufficient inventory for product: " . $row['product_name']);
        }
        $total_amount += $row['price'] * $row['quantity'];
        $cart_items[] = $row; // Store for order processing
    }

    // Create a new order
    $sql = "INSERT INTO orders (user_id, total_amount, order_date, order_status, payment_method, billing_address)
            VALUES (?, ?, NOW(), 'Pending', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $total_amount, $payment_method, $billing_address);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Log each product sale in the sales table
        foreach ($cart_items as $item) {
            $sale_amount = $item['price'] * $item['quantity'];
            $sql = "INSERT INTO sales (product_id, quantity, sale_amount) 
                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iid", $item['product_id'], $item['quantity'], $sale_amount);
            $stmt->execute();
        }

        // Add products to the order_items table
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity)
                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $order_id, $item['product_id'], $item['quantity']);
            $stmt->execute();
        }

        // Deduct inventory
        foreach ($cart_items as $item) {
            $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
        }

        // Clear the user's cart
        $sql = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Redirect to the shop page
        header("Location: shop.php");
    } else {
        echo "Failed to process checkout!";
    }
?>
