<?php
    session_start();
    include 'session_check.php';
    include 'db.php'; // Include database connection

    // Fetch item details based on the ID
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM inventory WHERE id = $id";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $item = mysqli_fetch_assoc($result);
        } else {
            die("Item not found.");
        }
    }

    // Handle form submission to update the item
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id']);
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // If the user uploads a new image, use the new image path
        $image_path = $item['image_path'];  // Default to current image if no new upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $product_image_dir = 'product_images/';
            $image_name = basename($_FILES['image']['name']);
            $image_path = $product_image_dir . uniqid() . '-' . $image_name;

            // Ensure the upload directory exists
            if (!is_dir($product_image_dir)) {
                mkdir($product_image_dir, 0755, true);
            }

            // Move the uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                die("Failed to upload image.");
            }
        }

        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);

        // Update the item in the database
        $sql = "UPDATE inventory 
                SET product_name = '$product_name', description = '$description', image_path = '$image_path', price = $price, quantity = $quantity 
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            echo "Item updated successfully!";
            $category = $_POST['category'] ?? 'Items'; // Get category from request
            header("Location: admin-dashboard.php?category=$category");
            exit;
        } else {
            echo "Error updating item: " . mysqli_error($conn);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
</head>
<body>
    <section id="navbar">
        <ul>
            <li><a href="admin.html">Admin</a></li>
            <li><a href="index.html" style="margin-right: 40px;">Logout</a></li>
        </ul>
    </section>
    <section id="main">
    <h2>Edit Product: <?php echo htmlspecialchars($item['product_name']); ?></h2>
        <form action="edit-item.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($item['product_name']); ?>" required><br>

            <label for="description">Description:</label>
            <textarea name="description" rows="3" required><?php echo htmlspecialchars($item['description']); ?></textarea><br>

            <label for="image">Image:</label>
            <!-- Display the existing image if available -->
            <?php if (!empty($item['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product Image" style="width:100px;height:auto;"><br>
                <span>Current Image</span><br>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*"><br>

            <label for="price">Price:</label>
            <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($item['price']); ?>" required><br>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" min="0" value="<?php echo htmlspecialchars($item['quantity']); ?>" required><br>

            <button type="submit">Update Product</button>
        </form>
    </section>
    <style>
        * {
            font-family: 'Roboto', sans-serif;
            margin: 0;
        }

        body {
            width: 100%;
            height: 100%;
            background-color: #efeaea;
        }

        section[id="navbar"] {
            display: flex;
            align-items: center;
            position: fixed;
            width: 100%;
            height: 50px;
            background-color: #333;
            z-index: 1;
        }

        section[id="navbar"] ul {
            list-style: none;
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin: 10px;
        }

        section[id="navbar"] a {
            color: white;
            text-decoration: none;
        }

        section[id="main"] {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 50px);
        }

        #main {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 40%;
            margin: 20px auto;
        }

        form label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
        }

        form input, form select {
            width: 100%;
            height: 30px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
        }

        form button:hover {
            background-color: #45a049;
        }
    </style>
</body>
</html>
