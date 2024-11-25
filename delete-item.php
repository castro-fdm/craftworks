<?php
include 'db.php'; // Include database connection

// Check if the item ID is set in the query parameter
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];

    // Delete the user from the database
    $sql = "DELETE FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        echo "Item deleted successfully!";
        header("Location: admin-dashboard.php");
        exit;
    } else {
        echo "Failed to delete item.";
    }
} else {
    echo "No item found!";
}
?>