<?php
require '../config/db.php';

$username = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "Admin account already exists.";
} else {
    $name = "Admin User";
    $email = "admin@example.com";
    $password = password_hash("admin123", PASSWORD_BCRYPT);
    $role = "admin";
    $status = "approved";

    $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $username, $password, $role, $status);

    if ($stmt->execute()) {
        echo "Admin account created successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
