<?php
include 'db.php'; // Include database connection

// Query to get all users from the database
$sql = "SELECT * FROM users"; // Adjust the query according to your table structure
$result = $conn->query($sql);

// Output the user data in an HTML table
if ($result->num_rows > 0) {
    echo "<table border='1'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Password</th> <!-- Add Password Column -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";
    while ($user = $result->fetch_assoc()) {
        // Mask the password for security
        $maskedPassword = "********"; // Mask the password

        echo "<tr>
                <td>" . $user['id'] . "</td>
                <td>" . $user['username'] . "</td>
                <td>" . $user['email'] . "</td>
                <td>" . $user['role'] . "</td>
                <td>" . $maskedPassword . "</td> <!-- Display Masked Password -->
                <td>
                    <a href='edit-users.php?id=" . $user['id'] . "'>Edit</a> | 
                    <a href='delete-users.php?id=" . $user['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </td>
            </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No users found.</p>";
}
?>
