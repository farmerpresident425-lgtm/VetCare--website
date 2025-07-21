<?php
session_start();
include '../config/db.php';

$groupId = $_POST['group_id'] ?? 0;
$userIds = $_POST['user_ids'] ?? [];

if ($groupId && is_array($userIds)) {
    foreach ($userIds as $userId) {
        $stmt = $conn->prepare("INSERT IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
    }
}

header("Location: main.php?section=group_chat");
exit;
