<?php
    session_start();
    include 'session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CraftWorks</title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <section id="navbarSection">
        <div class="logoContainer">
            <a id="logo-link" href="index.php"><img id="logo" src="/res/craftworks_logo.png" alt="Logo"></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="#">About Us</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <section id="backdropSection">
        <div class="image-container">
            <img src="/res/backdrop1hd.png" alt="" id="backdrop">
            <div class="textSlogan">Empower Through Art<br>
                <label style="font-size: 20px;">Discover unique handmade arts and crafts created by persons deprived of liberty.</label><br>
                <a href="shop.php"><button id="shopButton">Shop Now</button></a>
            </div>
        </div>
    </section>
    <section id="featuredProducts">
        <div class="featuredProductsContainer" style="display: inline-block; width: auto; height: auto;">
            <div class="featuredTitleContainer">
                <div class="featuredTitle">
                    <label style="font-weight: bold; font-size: 40px">Featured Products</label><br>
                    <p style="font-size: 15px; margin: 10px; color: #000;">Browse handcrafted products</p><br>
                    <a href="shop.php"><button>View All Products</button></a>
                </div>
            </div>
            <div class="productCardContainer">
                <div class="productCard">
                    <img src="https://placehold.co/300x200" alt="Massage Therapy" class="service-image">
                    <h3>Product Name</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vitae, quos nostrum rerum</p>
                    <button>Add to Cart</button>
                </div>
                <div class="productCard">
                    <img src="https://placehold.co/300x200" alt="Facial Treatment">
                    <h3>Product Name</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vitae, quos nostrum rerum</p>
                    <button>Add to Cart</button>
                </div>
                <div class="productCard">
                    <img src="https://placehold.co/300x200" alt="Spa Packages">
                    <h3>Product Name</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vitae, quos nostrum rerum</p>
                    <button>Add to Cart</button>
                </div>
            </div>
        </div>
    </section>
    <section id="customerSection">
        <div class="customerContainer">
            <div class="customerTitle">
                <label style="font-weight: bold; font-size: 40px">Customer Reviews</label><br><label style="font-size: 15px;">What our customers say</label>
            </div>
            <div class="feedback-card-container">
            <div class="feedback-card">
                <p>"Lorem ipsum dolor sit amet consectetur adipisicing elit."</p>
                <span>- Franz Castro</span>
            </div>
            <div class="feedback-card">
                <p>"Lorem ipsum dolor sit amet consectetur adipisicing elit."</p>
                <span>- Paul Valera</span>
            </div>
            <div class="feedback-card">
                <p>"Lorem ipsum dolor sit amet consectetur adipisicing elit."</p>
                <span>- Alvic palongyas</span>
            </div>
        </div>
        </div>
    </section>
    <section id="footerSection">
        <div class="footerContainer">
            <div class="footerLogoContainer">
                <img src="/res/craftworks_logo.png" alt="Logo" class="footer-logo">
                <div class="contactContainer">
                    <h4>Contact Us</h4>
                    <p>Address: 123 Main St, City, Country</p>
                    <p>Phone: +1 (123) 456-7890</p>
                    <p>Email: support@example.com</p>
                </div>
            </div>
            <div class="quickLinksContainer">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>
            <div class="newsletterContainer">
                <h4>Subscribe to Our Newsletter</h4>
                <input type="email" placeholder="Email Address" id="newsletterEmail">
                <button>Subscribe</button>
                <div class="social-icons">
                    <a href="#"><img src="/res/facebook.png" alt="Facebook"></a>
                    <a href="#"><img src="/res/twitter.png" alt="Twitter"></a>
                    <a href="#"><img src="/res/instagram.png" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <div class="footerBottom">
            <p>&copy; 2024 CraftWorks. All Rights Reserved.</p>
        </div>
    </section>     
</body>
</html>