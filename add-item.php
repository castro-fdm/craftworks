<?php
session_start();
include 'db.php'; // Include database connection

// Check if the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $product_name = mysqli_real_escape_string($conn, trim($_POST['product_name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $product_image_dir = __DIR__ . '/product_images/';
        $image_name = uniqid() . '-' . basename($_FILES['image']['name']);
        $image_path = $product_image_dir . $image_name;

        // Ensure the upload directory exists
        if (!is_dir($product_image_dir)) {
            if (!mkdir($product_image_dir, 0755, true)) {
                die("Failed to create directory: $product_image_dir. Check permissions.");
            }
        }        

        // Move the uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            die("Failed to upload image. Check directory permissions.");
        }

        // Convert the image path to a relative path for database storage
        $image_path = 'product_images/' . $image_name;
    } else {
        die("Error uploading image: " . ($_FILES['image']['error'] ?? 'Unknown error.'));
    }

    // Insert data into the database
    $sql = "INSERT INTO inventory (product_name, description, image_path, price, quantity)
            VALUES ('$product_name', '$description', '$image_path', '$price', '$quantity')";

    if (mysqli_query($conn, $sql)) {
        header("Location: admin-dashboard.php?category=Items");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item</title>
</head>
<body>
    <h2>Add New Product</h2>
    <form action="add-item.php" method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" required><br>

        <label for="description">Description:</label>
        <textarea name="description" rows="3" required></textarea><br>

        <label for="image">Image:</label>
        <input type="file" name="image" accept="image/*" required><br>

        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" min="0" required><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" min="0" required><br>

        <button type="submit">Add Product</button>
    </form>
</body>
</html>
