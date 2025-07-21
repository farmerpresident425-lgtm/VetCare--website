<?php
session_start();
include '../config/db.php';

$senderId = $_SESSION['user_id'] ?? 0;
$groupId = $_POST['group_id'] ?? 0;
$message = trim($_POST['message']);

if ($senderId && $groupId && $message !== '') {
    $stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message, time_sent) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $groupId, $senderId, $message);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
