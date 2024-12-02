<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Initialize cart_items and total_amount
$cart_items = [];
$total_amount = 0;

// Fetch all items in the user's cart
$sql = "SELECT c.id AS cart_id, i.product_name, i.price, c.quantity, (i.price * c.quantity) AS total
        FROM cart c
        JOIN inventory i ON c.product_id = i.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;  // Add items to cart array
        $total_amount += $row['total'];  // Calculate total amount
    }
} else {
    header("Location: shop.php");
    exit;
}

// Handle updates to product quantities, and save payment method, billing address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart_id'], $_POST['quantity'], $_POST['payment_method'], $_POST['billing_address'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        $payment_method = $_POST['payment_method'];
        $billing_address = $_POST['billing_address'];

        // Sanitize billing address
        $billing_address = mysqli_real_escape_string($conn, $_POST['billing_address']);

        // Update the cart item quantity
        if ($quantity > 0) {
            $sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            $stmt->execute();
        } else {
            // Remove item from cart if quantity is set to 0
            $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cart_id, $user_id);
            $stmt->execute();
        }

        // Insert order with payment method and billing address
        // Collect product names as a string
        $product_names = implode(',', array_column($cart_items, 'product_name'));

        // Insert order into orders table
        $sql = "INSERT INTO orders (user_id, total_amount, payment_method, billing_address, product_names)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idsss", $user_id, $total_amount, $payment_method, $billing_address, $product_names);

        // Begin a transaction to ensure all operations are completed successfully
        $conn->begin_transaction();

        try {
            // Execute the insert
            $stmt->execute();
            $order_id = $stmt->insert_id;

            // Deduct inventory for each item in the cart
            foreach ($cart_items as $item) {
                $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $item['quantity'], $item['cart_id']);
                $stmt->execute();
            }

            // Clear the cart after checkout
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Commit the transaction if everything went well
            $conn->commit();
            header("Location: shop.php");
        } catch (Exception $e) {
            // Rollback the transaction if anything went wrong
            $conn->rollback();
            echo "Failed to process checkout: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        #navbarSection {
            display: flex;
            flex-direction: row;
            background-color: #fff;
            padding: 10px;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        #logo-link {
            text-decoration: none;
            color: #fff;
        }

        #logo {
            width: 100%;
            height: 100%;
            margin: auto;
        }

        .logoContainer {
            display: flex;
            align-items: center;
            width: 200px;
            height: 50px;
        }

        nav {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li a {
            text-decoration: none;
            color: #000000;
            position: relative;
            font-weight: bold;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-top: 30px;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        td input[type="number"] {
            width: 60px;
            padding: 5px;
            text-align: center;
        }

        td button {
            padding: 6px 12px;
            background-color: #4CAF50;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        td button:hover {
            background-color: #45a049;
        }

        .total {
            text-align: right;
            font-weight: bold;
            padding: 10px;
            background-color: #f4f4f4;
        }

        .checkout-container {
            display: flex;
            justify-content: space-between;
            padding: 20px 15%;
        }

        .checkout-container div {
            flex: 1;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            box-sizing: border-box;
        }

        input[type="radio"] {
            padding: 8px;
            margin: 8px 0;
            box-sizing: border-box;
        }

        button.checkout-btn {
            display: inline-block;
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            font-weight: bold;
        }

        button.checkout-btn:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>
    <section id="navbarSection">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>

    <h1>Your Cart</h1>
    <form method="POST" action="checkout.php">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cart_items)): ?>
                    <tr>
                        <td colspan="5">Your cart is empty.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" onchange="updateQuantity(this, <?= $item['cart_id'] ?>, <?= $item['price'] ?>)">
                            </td>
                            <td>$<span id="total-<?= $item['cart_id'] ?>"><?= number_format($item['total'], 2) ?></span></td>
                            <td>
                                <button type="button" onclick="removeItem(<?= $item['cart_id'] ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="checkout-container">
            <div style="display: flex; flex-direction: column;">
                <h3>Billing Information</h3>
                <input type="text" name="billing_address" placeholder="Billing Address" required>
                <h3>Payment Method</h3>
                <div>
                    <label><input type="radio" name="payment_method" value="Cash on Delivery" required> Cash on Delivery</label><br>
                    <label><input type="radio" name="payment_method" value="E-wallet" required> E-wallet</label>
                </div>
                <div class="total">
                    Total Amount: ₱<span id="total-amount"><?= number_format($total_amount, 2) ?></span>
                </div>
                <button type="submit" class="checkout-btn" href="checkout.php">Proceed to Checkout</button>
            </div>
        </div>
    </form>
    <script>
        function updateQuantity(input, cartId, price) {
            const quantity = parseInt(input.value);
            const totalCell = document.getElementById('total-' + cartId);
            const total = quantity * price;
            totalCell.innerText = total.toFixed(2);

            updateTotalAmount();
        }

        function removeItem(cartId) {
            if (confirm("Are you sure you want to remove this item?")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'cart.php';

                const cartIdInput = document.createElement('input');
                cartIdInput.type = 'hidden';
                cartIdInput.name = 'cart_id';
                cartIdInput.value = cartId;
                form.appendChild(cartIdInput);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = 'quantity';
                quantityInput.value = 0;
                form.appendChild(quantityInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateTotalAmount() {
            let totalAmount = 0;
            document.querySelectorAll('table tbody tr').forEach(row => {
                const total = parseFloat(row.querySelector('td:nth-child(4) span').innerText.replace('$', ''));
                totalAmount += total;
            });
            document.getElementById('total-amount').innerText = totalAmount.toFixed(2);
        }
    </script>
</body>
</html>