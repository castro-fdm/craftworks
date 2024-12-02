<?php
    session_start();
    include 'session_check.php';
    require 'db.php';

    header('Content-Type: application/json');

    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Fetch all products from the database
    $query = "SELECT id, product_name, description, image_path, price, quantity FROM inventory ORDER BY id ASC";
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["error" => $conn->error]);
        exit;
    }

    $products = [];
    // Fetch products and ensure the price is a float
    while ($row = $result->fetch_assoc()) {
        $row['price'] = (float)$row['price']; // Ensure numeric value
        $products[] = $row;
    }

    echo json_encode($products);
?>
