<?php
header('Content-Type: application/json');

$response = [
    "success" => false,
    "errors" => [
        "username" => "",
        "email" => "",
        "phone" => ""
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php'; // Database connection

    $username = trim($_POST['usernameField']);
    $email = trim($_POST['emailField']);
    $phone = trim($_POST['phoneField']);
    $password = trim($_POST['passwordField']);

    // Check for duplicates
    $query = "SELECT username, email, phone_number FROM users WHERE username = ? OR email = ? OR phone_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($existingUsername, $existingEmail, $existingPhone);
        $stmt->fetch();

        if ($username === $existingUsername) {
            $response["errors"]["username"] = "Username is already taken.";
        }
        if ($email === $existingEmail) {
            $response["errors"]["email"] = "Email is already in use.";
        }
        if ($phone === $existingPhone) {
            $response["errors"]["phone"] = "Phone number is already in use.";
        }
    } else {
        // If no duplicates, insert the user
        $insertQuery = "INSERT INTO users (username, email, phone_number, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $username, $email, $phone, $hashedPassword);

        if ($stmt->execute()) {
            $response["success"] = true;
        } else {
            $response["errors"]["general"] = "Error occurred during registration.";
        }
    }

    $stmt->close();
    $conn->close();
}

// Return JSON response
echo json_encode($response);
?>