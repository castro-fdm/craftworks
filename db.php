<?php
    $servername = "localhost:3306";
    $username = "root";
    $password = "Hotwheels06";
    $dbname = "user_db";

    //create connection

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
