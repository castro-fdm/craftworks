<?php
    // Include database connection
    include 'db.php';

    // Query to fetch orders along with user details and the products in the order
    $sql = "
        SELECT 
            orders.id AS order_id, 
            users.username, 
            orders.total_amount, 
            orders.order_status, 
            orders.order_date, 
            orders.payment_method, 
            orders.billing_address, 
            GROUP_CONCAT(inventory.product_name SEPARATOR ', ') AS product_names
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN order_items ON orders.id = order_items.order_id
        JOIN inventory ON order_items.product_id = inventory.id
        GROUP BY orders.id
    ";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Order ID</th><th>User</th><th>Products</th><th>Payment Method</th><th>Billing Address</th><th>Order Created</th><th>Total Amount</th><th>Order Status</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['order_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['product_names']) . "</td>";
            echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
            echo "<td>" . htmlspecialchars($row['billing_address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['order_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_amount']) . "</td>";
            echo "<td>" . htmlspecialchars($row['order_status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No order data found.</p>";
    }

    $conn->close();
?>
