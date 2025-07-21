<?php
include '../config/db.php';
session_start();

$senderId = $_SESSION['user_id'] ?? 0;
$message = trim($_POST['message'] ?? '');
$groupId = intval($_POST['group_id'] ?? 0);

if (!$senderId || !$groupId || empty($message)) {
    header("Location: main.php?section=group_chat");
    exit;
}

$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $groupId, $senderId, $message);
$stmt->execute();

header("Location: main.php?section=group_chat");
exit;
