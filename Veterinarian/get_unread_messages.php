<?php
session_start();
include '../config/db.php';

$userId = $_SESSION['user_id'] ?? 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM messages WHERE receiver_id = $userId AND is_read = 0 AND is_group = 0");
$count = $result->fetch_assoc()['total'] ?? 0;

echo $count;
