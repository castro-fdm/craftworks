<?php
include 'db.php'; // Include database connection

// Fetch all items from the database
$sql = "SELECT * FROM inventory ORDER BY id DESC"; // Order by latest
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo '<style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            text-align: left;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            word-wrap: break-word;
        }
        table th {
            background-color: #f4f4f4;
        }
        table td img {
            width: 100px;
            height: auto;
        }
        table td button {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }
        table td button:hover {
            background-color: #ddd;
        }
        table td a button {
            text-decoration: none;
        }
        @media screen and (max-width: 600px) {
            table {
                font-size: 14px;
            }
            table td, table th {
                padding: 6px;
            }
            table td button {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
    </style>';
    
    echo '<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Product Image</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['product_name']) . '</td>
                <td><img src="' . htmlspecialchars($row['image_path']) . '" alt="Product Image"></td>
                <td>' . htmlspecialchars($row['description']) . '</td>
                <td>$' . number_format($row['price'], 2) . '</td>
                <td>' . htmlspecialchars($row['quantity']) . '</td>
                <td>
                    <a href="edit-item.php?id=' . $row['id'] . '">Edit</a> | 
                    <a href="delete-item.php?id=' . $row['id'] . '" class="delete" onclick="return confirm(\'Are you sure?\')">Delete</a>
                </td>
              </tr>';
    }
    echo '</tbody></table>';
    echo '<a href="add-item.php"><button>Add New Item</button></a>';
} else {
    echo '<a href="add-item.php"><button>Add New Item</button></a>';
}
?>
