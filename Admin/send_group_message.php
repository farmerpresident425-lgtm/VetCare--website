<?php
session_start();
require('../config/db.php');

// 🔐 Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("⛔ You must be logged in to send messages.");
}

$sender_id = $_SESSION['user_id'];
$group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// 🛡️ Basic validation
if ($group_id <= 0 || $message === '') {
    die("⚠️ Invalid group or empty message.");
}

// 🧼 Sanitize input (optional if already using prepared statement)
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// 💬 Insert message
$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $group_id, $sender_id, $message);

if ($stmt->execute()) {
    // ✅ Redirect back to group chat view
    header("Location: ../Admin/main.php?section=group_chat&group_id=" . $group_id);
    exit;
} else {
    // ❌ Show clean error (you can customize this for mobile too)
    echo "<div style='padding:1rem;font-family:sans-serif;color:#b91c1c;background:#fee2e2;border:1px solid #fca5a5;border-radius:0.5rem;max-width:500px;margin:auto;text-align:center;'>
            ❌ Failed to send message. Please try again.
          </div>";
}
?>
