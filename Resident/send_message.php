<?php
include '../config/db.php';
session_start();

$senderId = $_SESSION['user_id'] ?? 0;
$message = trim($_POST['message'] ?? '');
$receiverId = intval($_POST['receiver_id'] ?? 0);

if (!$senderId || !$receiverId || empty($message)) {
    header("Location: main.php?section=messages");
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $senderId, $receiverId, $message);
$stmt->execute();

header("Location: main.php?section=messages&chat_with=$receiverId");
exit;
