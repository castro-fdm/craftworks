<?php
    session_start();
    include 'session_check.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - CraftWorks</title>
    <link rel="stylesheet" href="/css/shop.css">
</head>
<body>
<section id="navbarSection">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul style="line-height: 30px;">
                <li><a href="index.php"><img style="width: 100%; max-width: 30px;" src="res/home.png" alt="home"></a></li>
                <li><a href="shop.php"><img style="width: 100%; max-width: 30px;" src="res/shopping-cart.png" alt="shop"></a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="cart.php"><img style="width: 100%; max-width: 30px;" src="res/shopping-bag.png" alt="cart"></a></li>
                    <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <section id="shopSection">
        <div id="filterSection">
            <h2>Filters</h2>
            <div class="filter">
                <label for="category">Category:</label>
                <select id="category">
                    <option value="all">All</option>
                    <option value="art">Art</option>
                    <option value="crafts">Crafts</option>
                </select>
            </div>
            <div class="filter">
                <label for="priceRange">Price Range:</label>
                <input type="range" id="priceRange" min="0" max="5000" value="2500">
                <span id="priceRangeLabel">₱0000</span>
            </div>
            <div class="filter">
                <label for="sort">Sort By:</label>
                <select id="sort">
                    <option value="low-to-high">Price: Low to High</option>
                    <option value="high-to-low">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div id="productsSection">
            <div class="header-section">
                <h1>Shop Products</h1>
                <input type="text" id="searchBar" placeholder="Search Products..." class="search-field">
            </div>
            <div class="productCards" id="productCards">
                <!-- Product cards will be dynamically injected here -->
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();

            // Update products on filter/sort/search changes
            document.getElementById('category').addEventListener('change', loadProducts);
            document.getElementById('priceRange').addEventListener('input', (e) => {
                document.getElementById('priceRangeLabel').textContent = `₱${e.target.value}`;
                loadProducts();
            });
            document.getElementById('sort').addEventListener('change', loadProducts);
            document.getElementById('searchBar').addEventListener('input', loadProducts);
        });

        function loadProducts() {
        const category = document.getElementById('category').value;
        const priceRange = document.getElementById('priceRange').value;
        const sort = document.getElementById('sort').value;
        const searchQuery = document.getElementById('searchBar').value.toLowerCase();

        fetch('./get-products.php')
            .then(response => response.text())  // Get raw response as text
            .then(text => {
                console.log(text);  // Log the raw text
                try {
                    const products = JSON.parse(text);
                    const filteredProducts = products.filter(product => {
                        const matchesCategory = category === 'all' || (product.category && product.category === category);
                        const matchesPrice = product.price <= priceRange;
                        const matchesSearch = product.product_name.toLowerCase().includes(searchQuery);
                        return matchesCategory && matchesPrice && matchesSearch;
                    });

                    if (sort === 'low-to-high') {
                        filteredProducts.sort((a, b) => a.price - b.price);
                    } else if (sort === 'high-to-low') {
                        filteredProducts.sort((a, b) => b.price - a.price);
                    }

                    displayProducts(filteredProducts);
                } catch (error) {
                    console.error('Failed to parse JSON:', error);
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
        }

        function displayProducts(products) {
            const productCardsContainer = document.getElementById('productCards');
            productCardsContainer.innerHTML = ''; // Clear existing products

            if (products.length === 0) {
                productCardsContainer.innerHTML = '<p>No products found.</p>';
                return;
            }

            products.forEach(product => {
                const productDiv = document.createElement('div');
                productDiv.className = 'product';

                const price = product.price ? parseFloat(product.price) : 0;

                productDiv.innerHTML = `
                    <img src="${product.image_path}" alt="${product.product_name}">
                    <h3>${product.product_name}</h3>
                    <p>${product.description}</p>
                    <p class="price">₱${price.toFixed(2)}</p>
                    <button class="add-to-cart" data-id="${product.id}" data-quantity="1">Add to Cart</button>
                `;

                productCardsContainer.appendChild(productDiv);
            });

            // Attach event listeners to buttons
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', (event) => {
                    const productId = event.target.getAttribute('data-id');
                    const quantity = event.target.getAttribute('data-quantity');

                    // Send data to the server
                    fetch('add-to-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `product_id=${productId}&quantity=${quantity}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data); // Display server response
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            });
        }
    </script>
</body>
</html>