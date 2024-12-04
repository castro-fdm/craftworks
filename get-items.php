<?php
    session_start();
    include 'db.php'; // Include database connection

    // Fetch all items from the database
    $sql = "SELECT * FROM inventory ORDER BY id ASC"; // Order by ascending
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<style>
            table {
                width: auto;
                margin: 20px;
                border-collapse: collapse;
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
                width: 80px;
                height: auto;
            }
            table td {
                word-wrap: break-word; /* Prevent overflow in cells */
            }

            /* Restricting the Actions column width */
            table td.actions {
                width: 150px;
                text-align: center; /* Center the actions */
            }

            table td a {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 5px;
                text-align: center;
                margin: 2px;
            }

            /* Responsive adjustments */
            @media screen and (max-width: 600px) {
                table {
                    font-size: 14px;
                }
                table td, table th {
                    padding: 6px;
                }
                table td a {
                    font-size: 12px;
                    padding: 4px 8px;
                }
                /* Stack the actions column to avoid overflow */
                table td.actions {
                    width: 100%; /* Make the actions column take full width on small screens */
                }
                table td {
                    word-wrap: normal; /* Allow wrapping for all content */
                }
            }

            .add-button-container button {
                padding: 10px 20px;
                font-size: 16px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .add-button-container button:hover {
                background-color: #45a049;
            }
        </style>';

        // Output the item data in an HTML table
        echo '<table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Product Image</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['id']) . '</td>
                    <td>' . htmlspecialchars($row['product_name']) . '</td>
                    <td><img src="' . htmlspecialchars($row['image_path']) . '" alt="Product Image"></td>
                    <td>' . htmlspecialchars($row['description']) . '</td>
                    <td>'."₱" . number_format($row['price'], 2) . '</td>
                    <td>' . htmlspecialchars($row['quantity']) . '</td>
                    <td class="actions">
                        <a href="edit-item.php?id=' . $row['id'] . '">Edit</a> | 
                        <a href="delete-item.php?id=' . $row['id'] . '" class="delete" onclick="return confirm(\'Are you sure?\')">Delete</a>
                    </td>
                </tr>';
        }
        echo '</tbody></table>';
        echo '<div class="add-button-container">
                <a href="add-item.php"><button>Add New Item</button></a>
            </div>';
    } else {
        echo '<div class="add-button-container">
                <a href="add-item.php"><button>Add New Item</button></a>
            </div>';
    }
?>
