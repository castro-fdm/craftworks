<?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $servername = "database-1.czcee4o6obzg.ap-southeast-1.rds.amazonaws.com";
    $username = "admin";
    $password = "Hotwheels_06"; // change this to your password
    $dbname = "ecommerce_db";

    // Create connection to MySQL server (without specifying a database)
    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create the database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "";
    } else {
        die("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db($dbname);

    // Create the Users table with a role column
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone_number VARCHAR(15),
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user' -- Role column for RBAC
    )";
    if (!$conn->query($sql)) {
        die("Error creating users table: " . $conn->error);
    }

    // Create the Inventory table
    $sql = "CREATE TABLE IF NOT EXISTS inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(150) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 0
    )";
    if (!$conn->query($sql)) {
        die("Error creating inventory table: " . $conn->error);
    }

    // Create the Payments table
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        payment_method ENUM('COD') NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
    )";
    if (!$conn->query($sql)) {
        die("Error creating payments table: " . $conn->error);
    }

    // Insert a sample admin user for testing
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT); // Securely hash passwords
    $sql = "INSERT INTO users (username, email, phone_number, password, role) 
            VALUES ('admin', 'admin@example.com', '1234567890', '$adminPassword', 'admin')
            ON DUPLICATE KEY UPDATE email = email"; // Prevent duplicate insertion
    if (!$conn->query($sql)) {
        die("Error creating admin user: " . $conn->error);
    }

?>
