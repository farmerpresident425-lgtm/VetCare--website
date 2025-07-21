<?php
session_start();
include __DIR__ . '/../config/db.php'; // Safe path

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: ../index.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['profile']['tmp_name'];
    $fileName = basename($_FILES['profile']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExt, $allowed)) {
        echo "Invalid file type.";
        exit;
    }

    // Uploads folder (one level up from resources/)
    $uploadDir = realpath(__DIR__ . '/../uploads/');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = 'user_' . $userId . '.' . $fileExt;
    $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

    if (move_uploaded_file($fileTmp, $uploadPath)) {
        $relativePath = 'uploads/' . $newFileName;

        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $relativePath, $userId);
        $stmt->execute();

        // Correct redirect path (fix spelling of 'Veterinarian')
        header("Location: ../dashboard/Veterinarian/veterinarian.php");
        exit;
    } else {
        echo "Failed to move uploaded file.";
        exit;
    }
} else {
    echo "No file uploaded or upload error.";
    exit;
}
