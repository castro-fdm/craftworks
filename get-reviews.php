<?php
    include 'db.php'; // Include database connection

    // SQL query to fetch reviews along with product names and reviewer names
    $query = "
        SELECT 
            reviews.id, 
            inventory.product_name, 
            reviews.rating, 
            reviews.review_date, 
            reviews.review_text AS reviewer_text,
            users.username AS reviewer_name 
        FROM reviews
        JOIN orders ON reviews.order_id = orders.id
        JOIN order_items ON orders.id = order_items.order_id -- Join with the order_items table
        JOIN inventory ON order_items.product_id = inventory.id -- Get product details from inventory
        JOIN users ON reviews.user_id = users.id
        ORDER BY reviews.id ASC
    ";
    
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<thead>';
        echo '<tr><th>ID</th><th>Product</th><th>Rating</th><th>Reviewer</th><th>Review</th><th>Date</th><th>Action</th></tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['rating']) . ' / 5</td>';
            echo '<td>' . htmlspecialchars($row['reviewer_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['reviewer_text']) . '</td>';
            echo '<td>' . htmlspecialchars($row['review_date']) . '</td>';
            echo '<td><a href="delete-review.php?id=' . $row['id'] . '" onclick="return confirm(\'Are you sure you want to delete this review?\')">Delete</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No reviews found.</p>';
    }

    $conn->close();
?>
