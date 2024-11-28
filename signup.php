<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include 'db.php'; // Include database connection

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        header('Content-Type: application/json'); // Send JSON responses

        // Retrieve and sanitize POST values
        $username = trim($_POST['usernameField']);
        $email = trim($_POST['emailField']);
        $phone_number = trim($_POST['phoneField']);
        $passwordField = trim($_POST['passwordField']);
        $password = password_hash($passwordField, PASSWORD_DEFAULT);

        // Validate required fields
        if (empty($username) || empty($email) || empty($phone_number) || empty($passwordField)) {
            echo json_encode(["error" => "All fields are required!"]);
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "Invalid email format!"]);
            exit();
        }

        // Check if username, email, or phone number already exists
        $checkQuery = "SELECT username, email, phone_number FROM users WHERE username = ? OR email = ? OR phone_number = ?";
        $stmt = $conn->prepare($checkQuery);

        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $phone_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($existingUsername, $existingEmail, $existingPhone);
                $stmt->fetch();

                // Determine which field is taken and return the appropriate error
                if ($username === $existingUsername) {
                    echo json_encode(["error" => "Username already exists!"]);
                } elseif ($email === $existingEmail) {
                    echo json_encode(["error" => "Email already exists!"]);
                } elseif ($phone_number === $existingPhone) {
                    echo json_encode(["error" => "Phone number already exists!"]);
                }

                $stmt->close();
                $conn->close();
                exit();
            }
            $stmt->close();
        } else {
            echo json_encode(["error" => "Error preparing query: " . $conn->error]);
            $conn->close();
            exit();
        }

        // Insert new user into the database
        $insertUserQuery = "INSERT INTO users (username, email, phone_number, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertUserQuery);

        if ($stmt) {
            $stmt->bind_param("ssss", $username, $email, $phone_number, $password);

            if ($stmt->execute()) {
                echo json_encode(["success" => "User registered successfully!"]);
            } else {
                echo json_encode(["error" => "Error inserting user: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Error preparing insert query: " . $conn->error]);
        }

        // Close the database connection
        $conn->close();
    }
?>
