<?php
session_start();
include '../config/db.php';

// 🔒 Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if ($receiver_id && $message !== '') {
    // 🧼 Optional sanitize (if needed)
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $safeMessage);
    $stmt->execute();
    $stmt->close();

    // ✅ Redirect to chat view
    header("Location: ../veterinarian/main.php?section=messages&chat_with=$receiver_id");
    exit;
} else {
    // ❌ Missing input error (styled for mobile if viewed directly)
    echo "<div style='padding:1rem;font-family:sans-serif;color:#b91c1c;background:#fee2e2;border:1px solid #fca5a5;border-radius:0.5rem;max-width:500px;margin:auto;text-align:center;'>
            ❌ Missing message or receiver.
          </div>";
}
?>
