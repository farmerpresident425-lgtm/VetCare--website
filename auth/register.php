<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role = trim($_POST['role'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $purok = trim($_POST['purok'] ?? '');

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username already taken.";
        header("Location: ../index.php");
        exit;
    }

    // 🔍 Check if an admin already exists
    $adminCheck = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");

    // ✅ Auto-approve if this is the first admin
    if ($role === 'admin' && $adminCheck->num_rows == 0) {
        $status = 'approved';
    } else {
        $status = 'pending';
    }

    // ✅ Insert user with computed status
    $stmt = $conn->prepare("INSERT INTO users (name, email, username, password, role, birth_date, address, purok, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $email, $username, $password, $role, $birthdate, $address, $purok, $status);

    if ($stmt->execute()) {
        $_SESSION['error'] = ($status === 'approved') 
            ? "Registration successful. You are now an admin."
            : "Registration successful. Wait for admin approval.";
    } else {
        $_SESSION['error'] = "Something went wrong. Try again.";
    }

    header("Location: ../index.php");
    exit;
}
?>
