<?php
    include 'db.php';

    $sql_weekly = "SELECT product_id, SUM(quantity) AS total_sales
                FROM sales
                WHERE sale_date >= CURDATE() - INTERVAL 7 DAY
                GROUP BY product_id";
    $weekly_result = $conn->query($sql_weekly);

    $sql_monthly = "SELECT product_id, SUM(quantity) AS total_sales
                    FROM sales
                    WHERE sale_date >= CURDATE() - INTERVAL 1 MONTH
                    GROUP BY product_id";
    $monthly_result = $conn->query($sql_monthly);

    $sql_trends = "SELECT product_id, AVG(quantity) AS avg_sales
                FROM sales
                WHERE sale_date >= CURDATE() - INTERVAL 30 DAY
                GROUP BY product_id";
    $trends_result = $conn->query($sql_trends);

    // Prepare data for display
    $weekly_sales = [];
    while ($row = $weekly_result->fetch_assoc()) {
        $weekly_sales[$row['product_id']] = $row['total_sales'];
    }

    $monthly_sales = [];
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_sales[$row['product_id']] = $row['total_sales'];
    }

    $future_trends = [];
    while ($row = $trends_result->fetch_assoc()) {
        $future_trends[$row['product_id']] = $row['avg_sales'];
    }

    // Return the data as HTML
    echo "<h3>Weekly Sales</h3><ul>";
    foreach ($weekly_sales as $product_id => $sales) {
        echo "<li>Product $product_id: $sales sales</li>";
    }
    echo "</ul>";

    echo "<h3>Monthly Sales</h3><ul>";
    foreach ($monthly_sales as $product_id => $sales) {
        echo "<li>Product $product_id: $sales sales</li>";
    }
    echo "</ul>";

    echo "<h3>Future Sales Trends</h3><ul>";
    foreach ($future_trends as $product_id => $avg_sales) {
        echo "<li>Product $product_id: Estimated avg. sales per day: $avg_sales</li>";
    }
    echo "</ul>";
?>
