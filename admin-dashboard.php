<?php
    session_start();
    include 'db.php'; // Include database connection
    $currentCategory = $_GET['category'] ?? 'Dashboard'; // Default to Dashboard if no category is specified

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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
                    <li><a href="admin-dashboard.php?category=Users">Users</a></li>
                </div>
                <div class="category-container">
                    <li><a href="admin-dashboard.php?category=Items">Items</a></li>
                </div>
                <div class="category-container">
                    <li><a href="admin-dashboard.php?category=Orders">Orders</a></li>
                </div>
                <div class="category-container">
                    <li><a href="admin-dashboard.php?category=Product-Analytics">Product Analysis</a></li>
                </div>
                <div class="category-container">
                    <li><a href="admin-dashboard.php?category=Reviews">Reviews</a></li>
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
            } else if (category === "Orders") {
                loadOrders(contentDiv);
            } else if (category === "Product-Analytics") {
                loadProductAnalysis(contentDiv);
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

        // Function to load order data
        function loadOrders(contentDiv) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get-orders.php', true); // Fetch order data
            xhr.onload = function() {
                if (xhr.status === 200) {
                    contentDiv.innerHTML = xhr.responseText;
                } else {
                    contentDiv.innerHTML = "<p>Failed to load order data.</p>";
                }
            };
            xhr.send();
        }

        // Function to load order data
        function loadProductAnalysis(contentDiv) {
            fetch('get-product-analytics.php')
                .then(response => response.json())
                .then(data => {
                    // Render the charts using Google Charts
                    google.charts.load('current', { packages: ['corechart'] });
                    google.charts.setOnLoadCallback(() => {
                        drawWeeklySalesChart(data.weekly);
                        drawMonthlySalesChart(data.monthly);
                        drawFutureTrendsChart(data.trends);
                    });

                    // Clear previous content and add chart containers
                    contentDiv.innerHTML = `
                        <div id="weekly-sales-chart" style="width: 100%; height: 400px;"></div>
                        <div id="monthly-sales-chart" style="width: 100%; height: 400px;"></div>
                        <div id="future-trends-chart" style="width: 100%; height: 400px;"></div>
                        <button id="print-charts" style="margin-top: 20px;">Print Charts</button>
                    `;

                    // Print button functionality
                    document.getElementById('print-charts').onclick = function () {
                        window.print();
                    };
                })
                .catch(error => {
                    contentDiv.innerHTML = '<p>Failed to load product analysis data.</p>';
                    console.error('Error fetching analytics data:', error);
                });
        }

        function drawWeeklySalesChart(data) {
            const dataTable = google.visualization.arrayToDataTable([
                ['Product Name', 'Total Sales'],
                ...data
            ]);

            const options = {
                title: 'Weekly Sales',
                hAxis: { title: 'Product Name' },
                vAxis: { title: 'Total Sales' },
                legend: 'none'
            };

            const chart = new google.visualization.ColumnChart(document.getElementById('weekly-sales-chart'));
            chart.draw(dataTable, options);
        }

        function drawMonthlySalesChart(data) {
            const dataTable = google.visualization.arrayToDataTable([
                ['Product Name', 'Total Sales'],
                ...data
            ]);

            const options = {
                title: 'Monthly Sales',
                hAxis: { title: 'Product Name' },
                vAxis: { title: 'Total Sales' },
                legend: 'none'
            };

            const chart = new google.visualization.ColumnChart(document.getElementById('monthly-sales-chart'));
            chart.draw(dataTable, options);
        }

        function drawFutureTrendsChart(data) {
            const dataTable = google.visualization.arrayToDataTable([
                ['Product Name', 'Avg. Sales'],
                ...data
            ]);

            const options = {
                title: 'Future Sales Trends',
                hAxis: { title: 'Product Name' },
                vAxis: { title: 'Avg. Sales Per Day' },
                legend: 'none',
                curveType: 'function' // Smooth curve for trend visualization
            };

            const chart = new google.visualization.LineChart(document.getElementById('future-trends-chart'));
            chart.draw(dataTable, options);
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
        // Automatically load the category on page load
        window.onload = function() {
            const category = "<?php echo htmlspecialchars($currentCategory, ENT_QUOTES, 'UTF-8'); ?>";
            displayCategory(category);
        };
    </script>
</body>
</html>
