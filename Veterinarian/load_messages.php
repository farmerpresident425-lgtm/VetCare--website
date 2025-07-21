<?php
session_start();
include '../config/db.php';

$userId = $_SESSION['user_id'] ?? 0;

$messages = $conn->query("
    SELECT m.*, u.name AS sender_name 
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE (m.is_group = 1 OR m.receiver_id = $userId OR m.sender_id = $userId)
    ORDER BY m.created_at ASC
");

while ($msg = $messages->fetch_assoc()):
?>
  <div class="mb-2">
    <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong>
    <?= htmlspecialchars($msg['message']) ?>
    <span class="text-gray-500 text-xs float-right"><?= date("H:i", strtotime($msg['created_at'])) ?></span>
  </div>
<?php endwhile; ?>
