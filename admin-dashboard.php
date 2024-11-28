<?php
    session_start();
    include 'session_check.php';
    include 'db.php'; // Include database connection

    // Check if the user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: admin-login.php");
        exit("Access denied");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/res/admin.js"></script>
    <title>Admin Dashboard</title>
</head>
<body>
    <section id="navbar">
        <ul>
            <li><a href="admin-dashboard.php">Admin</a></li>
            <li><a href="admin-login.php" style="margin-right: 40px;">Logout</a></li>
        </ul>
    </section>
    <section id="main">
        <div class="vertical-category">
            <div class="category-title">
                <h2>Categories</h2>
            </div>
            <ul>
                <div class="category-container">
                    <li><a href="#" onclick="displayCategory('Users')">Users</a></li>
                </div>
                <div class="category-container">
                    <li><a href="#" onclick="displayCategory('Items')">Items</a></li>
                </div>
                <div class="category-container">
                    <li><a href="#" onclick="displayCategory('Payments')">Payments</a></li>
                </div>
                <div class="category-container">
                    <li><a href="#" onclick="displayCategory('Product Analysis')">Product Analysis</a></li>
                </div>
                <div class="category-container">
                    <li><a href="#" onclick="displayCategory('Reviews')">Reviews</a></li>
                </div>
            </ul>
        </div>

        <!-- Info section to display dynamic content -->
        <div class="info-category" id="info-category">
            <h2 id="category-title">Welcome Admin!</h2>
            <div id="category-content">
                <!-- Content will be injected here by JavaScript -->
            </div>
        </div>
    </section>

    <script>
        // Function to display the category content
        function displayCategory(category) {
            // Change the title dynamically based on the category clicked
            document.getElementById('category-title').innerHTML = category;

            // Set up the content to be injected
            const contentDiv = document.getElementById('category-content');

            // Check which category was clicked
            if (category === "Users") {
                loadUsers(contentDiv);
            } else if (category === "Items") {
                loadItems(contentDiv);
            } else if (category === "Payments") {
                contentDiv.innerHTML = "<p>Payments data will be displayed here.</p>";
            } else if (category === "Product Analysis") {
                contentDiv.innerHTML = "<p>Product analysis data will be displayed here.</p>";
            } else if (category === "Reviews") {
                contentDiv.innerHTML = "<p>Reviews data will be displayed here.</p>";
            }
        }

        // Function to load user data dynamically
        function loadUsers(contentDiv) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get-users.php', true); // Fetch user data from the server
            xhr.onload = function() {
                if (xhr.status === 200) {
                    contentDiv.innerHTML = xhr.responseText;
                } else {
                    contentDiv.innerHTML = "<p>Failed to load user data.</p>";
                }
            };
            xhr.send();
        }

        // Function to load item data and add items form
        function loadItems(contentDiv) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get-items.php', true); // Fetch item data
            xhr.onload = function() {
                if (xhr.status === 200) {
                    contentDiv.innerHTML = xhr.responseText;
                } else {
                    contentDiv.innerHTML = "<p>Failed to load item data.</p>";
                }
            };
            xhr.send();
        }
        // Global function to delete an item
        function deleteItem(id) {
            if (confirm("Are you sure you want to delete this item?")) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete-item.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert(xhr.responseText);
                        displayCategory('Items'); // Reload Items category after deletion
                    } else {
                        alert("Failed to delete item.");
                    }
                };
                xhr.send('id=' + id);
            }
        }
    </script>
</body>
</html>
