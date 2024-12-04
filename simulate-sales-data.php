// simulate_sales_data.php
<?php
    include 'db.php'; // Include your database connection file

    function getRandomProductId($conn) {
        $result = $conn->query("SELECT id FROM inventory");
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id'];
        }
        return $ids[array_rand($ids)];
    }

    // Simulate data for the past 30 days
    for ($i = 0; $i < 1000; $i++) {
        $productId = getRandomProductId($conn); // Random product from inventory
        $quantity = rand(1, 10); // Random quantity sold
        $saleAmount = $quantity * rand(100, 500) / 10; // Random price multiplier
        $saleDate = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days"));

        $sql = "
            INSERT INTO sales (product_id, quantity, sale_amount, sale_date)
            VALUES ($productId, $quantity, $saleAmount, '$saleDate')
        ";
        if (!$conn->query($sql)) {
            echo "Error: " . $conn->error . "\n";
        }
    }

    echo "Simulated sales data inserted!";
?>
