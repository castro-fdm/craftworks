<?php
    include 'db.php';

    // Weekly sales query
    $sql_weekly = "
        SELECT inventory.product_name, SUM(sales.quantity) AS total_sales
        FROM sales
        JOIN inventory ON sales.product_id = inventory.id
        WHERE sales.sale_date >= CURDATE() - INTERVAL 7 DAY
        GROUP BY inventory.product_name";
    $weekly_result = $conn->query($sql_weekly);

    if ($weekly_result->num_rows == 0) {
        echo "No data found for weekly sales!";
    }

    // Monthly sales query
    $sql_monthly = "
        SELECT inventory.product_name, SUM(sales.quantity) AS total_sales
        FROM sales
        JOIN inventory ON sales.product_id = inventory.id
        WHERE sales.sale_date >= CURDATE() - INTERVAL 1 MONTH
        GROUP BY inventory.product_name";
    $monthly_result = $conn->query($sql_monthly);

    if ($monthly_result->num_rows == 0) {
        echo "No data found for monthly sales!";
    }

    // Future trends query
    $sql_trends = "
        SELECT inventory.product_name, AVG(sales.quantity) AS avg_sales
        FROM sales
        JOIN inventory ON sales.product_id = inventory.id
        WHERE sales.sale_date >= CURDATE() - INTERVAL 30 DAY
        GROUP BY inventory.product_name";
    $trends_result = $conn->query($sql_trends);

    if ($trends_result->num_rows == 0) {
        echo "No data found for future trends!";
    }

    // Prepare data for JSON output
    $data = [
        'weekly' => [],
        'monthly' => [],
        'trends' => [],
    ];

    // Fetch weekly sales data
    while ($row = $weekly_result->fetch_assoc()) {
        $data['weekly'][] = [$row['product_name'], (int)$row['total_sales']];
    }

    // Fetch monthly sales data
    while ($row = $monthly_result->fetch_assoc()) {
        $data['monthly'][] = [$row['product_name'], (int)$row['total_sales']];
    }

    // Fetch future trends data
    while ($row = $trends_result->fetch_assoc()) {
        $data['trends'][] = [$row['product_name'], (float)$row['avg_sales']];
    }

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($data);
?>
