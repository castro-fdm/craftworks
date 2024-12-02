<?php
    session_start();
    include 'db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        echo "Access denied: You must be logged in.";
        exit;
    }

    // Validate and sanitize input
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($product_id <= 0 || $quantity <= 0) {
        http_response_code(400); // Bad Request
        echo "Invalid product ID or quantity.";     
        exit;
    }

    // Check if product exists and has sufficient stock
    $sql = "SELECT quantity FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404); // Not Found
        echo "Product does not exist.";
        exit;
    }

    $product = $result->fetch_assoc();
    if ($product['quantity'] < $quantity) {
        http_response_code(409); // Conflict
        echo "Insufficient stock.";
        exit;
    }

    // Check if the product is already in the cart
    $sql = "SELECT id FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the quantity
        $sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
    } else {
        // Add a new entry
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    }

    if ($stmt->execute()) {
        echo "Item added to cart!";
    } else {
        http_response_code(500); // Internal Server Error
        echo "Failed to add item to cart.";
    }
?>
