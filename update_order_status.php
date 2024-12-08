<?php
    session_start();
    include 'db.php';

    if (!isset($_POST['order_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Order ID not provided."]);
        exit();
    }

    $order_id = intval($_POST['order_id']);
    $sql = "UPDATE orders SET order_status = 'Completed' WHERE id = ? AND order_status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["message" => "Order status updated successfully."]);
    } else {
        http_response_code(500); // Internal Server Error
        echo "Error: " . $stmt->error;  // This will display the specific MySQL error
        echo json_encode(["message" => "Failed to update order status."]);
        
    }
    $stmt->close();
    $conn->close();
?>
