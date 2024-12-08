<?php
    session_start();
    include 'db.php';

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['username'];

    // Fetch user ID
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
    } else {
        die("User not found.");
    }

    // Fetch orders: To Receive
    $sql_to_receive = "
        SELECT o.id, o.total_amount, o.order_date, o.order_status,
               GROUP_CONCAT(oi.product_id, ':', oi.quantity) AS product_details
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND o.order_status != 'Completed'
        GROUP BY o.id";
    $stmt_to_receive = $conn->prepare($sql_to_receive);
    $stmt_to_receive->bind_param("i", $user_id);
    $stmt_to_receive->execute();
    $to_receive_orders = $stmt_to_receive->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch orders: Completed
    $sql_completed = "
        SELECT o.id, o.total_amount, o.order_date, o.order_status,
               GROUP_CONCAT(oi.product_id, ':', oi.quantity) AS product_details
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND o.order_status = 'Completed'
        GROUP BY o.id";
    $stmt_completed = $conn->prepare($sql_completed);
    $stmt_completed->bind_param("i", $user_id);
    $stmt_completed->execute();
    $completed_orders = $stmt_completed->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt_to_receive->close();
    $stmt_completed->close();
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
</head>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        #navbarSection {
            display: flex;
            flex-direction: row;
            background-color: #f2f2f2;
            padding: 10px;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        #logo-link {
            text-decoration: none;
            color: #f2f2f2;   
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

        /* Sidebar */
        .sidebar {
            width: 200px;
            height: 200px;
            top: 0;
            left: 0;
            background-color: #fff;
            border-right: 1px solid #ddd;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            font-size: 18px;
            color: #444;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        /* Orders Section */
        .order-container {
            display: flex;
            padding: 20px;
            gap: 30px;
        }

        .order {
            background: #fff;
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .order h3 {
            margin-top: 0;
            font-size: 20px;
            color: #555;
        }

        .order p {
            margin: 8px 0;
            font-size: 16px;
        }

        /* Modal */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .modal.show {
            display: block;
        }

        .modal h3 {
            font-size: 18px;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 18px;
            color: #aaa;
        }

        .close:hover {
            color: #333;
        }
    </style>
<body>
    <section id="navbarSection">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><img style="width: 100%; max-width: 30px;" src="res/home.png" alt="home"></a></li>
                <li><a href="shop.php"><img style="width: 100%; max-width: 30px;" src="res/shopping-cart.png" alt="shop"></a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <div class="order-container">
        <div class="sidebar">
            <h2>Order Categories</h2>
            <ul>
                <li><a href="#" onclick="showOrders('to-receive')">To Receive</a></li>
                <li><a href="#" onclick="showOrders('completed')">Completed</a></li>
            </ul>
        </div>
        <!-- To Receive Orders -->
        <div id="to-receive" class="order-section">
            <h2>To Receive</h2>
            <?php if (empty($to_receive_orders)): ?>
                <p>You have no orders to receive.</p>
            <?php else: ?>
                <?php foreach ($to_receive_orders as $order): ?>
                    <div class="order" id="order-<?= $order['id'] ?>">
                        <h3>Order #<?= $order['id'] ?></h3>
                        <p><strong>Products:</strong> 
                            <?php
                                $products = explode(',', $order['product_details']);
                                foreach ($products as $product) {
                                    list($product_id, $quantity) = explode(':', $product);
                                    echo "Product ID: $product_id (Qty: $quantity)<br>";
                                }
                            ?>
                        </p>
                        <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                        <p><strong>Order Date:</strong> <?= date("F j, Y", strtotime($order['order_date'])) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
                        <button onclick="markAsReceived(<?= $order['id'] ?>)">Order Received</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Completed Orders -->
        <div id="completed" class="order-section" style="display:none;">
            <h2>Completed</h2>
            <?php if (empty($completed_orders)): ?>
                <p>You have no completed orders.</p>
            <?php else: ?>
                <?php foreach ($completed_orders as $order): ?>
                    <div class="order">
                        <h3>Order #<?= $order['id'] ?></h3>
                        <p><strong>Products:</strong> 
                            <?php
                                $products = explode(',', $order['product_details']);
                                foreach ($products as $product) {
                                    list($product_id, $quantity) = explode(':', $product);
                                    echo "Product ID: $product_id (Qty: $quantity)<br>";
                                }
                            ?>
                        </p>
                        <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
                        <p><strong>Order Date:</strong> <?= date("F j, Y", strtotime($order['order_date'])) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
                        
                        <!-- Review Form -->
                        <form action="product-review.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                            <label for="rating">Rate this item:</label>
                            <select name="rating" id="rating" required>
                                <option value="1">&#9733</option>
                                <option value="2">&#9733 &#9733</option>
                                <option value="3">&#9733 &#9733 &#9733</option>
                                <option value="4">&#9733 &#9733 &#9733 &#9733</option>
                                <option value="5">&#9733 &#9733 &#9733 &#9733 &#9733</option>
                            </select>
                            <label for="review">Write a review:</label>
                            <textarea name="review" id="review" rows="4" required></textarea>
                            <button type="submit">Submit Review</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showOrders(category) {
            document.querySelectorAll('.order-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(category).style.display = 'block';
        }

        showOrders('to-receive');

        function markAsReceived(orderId) {
            if (confirm("Are you sure you want to mark this order as received?")) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "update_order_status.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            const orderElement = document.getElementById(`order-${orderId}`);
                            orderElement.remove();
                            alert("Order marked as received.");
                        } else {
                            alert("Failed to update the order. Please try again.");
                        }
                    }
                };

                xhr.send(`order_id=${orderId}`);
            }
        }
    </script>
</body>
</html>