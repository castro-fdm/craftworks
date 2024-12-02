<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied");
}

$user_id = $_SESSION['user_id'];

// Handle updates to product quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart_id'], $_POST['quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);

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
    }
}

// Fetch all items in the user's cart
$sql = "SELECT c.id AS cart_id, i.product_name, i.price, c.quantity, (i.price * c.quantity) AS total
        FROM cart c
        JOIN inventory i ON c.product_id = i.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_amount += $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Your Cart</h1>
    <form method="POST" action="cart.php">
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
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" required>
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                            </td>
                            <td><?= number_format($item['total'], 2) ?></td>
                            <td>
                                <button type="submit">Update</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total">Grand Total:</td>
                    <td colspan="2"><?= number_format($total_amount, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </form>
    <a href="checkout.php">Proceed to Checkout</a>
</body>
</html>
