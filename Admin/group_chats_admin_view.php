<?php
require '../config/db.php';


$groupId = $_GET['group_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$groupId || !$userId) {
    echo "<p class='text-red-500 text-center mt-4'>Missing group or user.</p>";
    return;
}

// 🔍 Get group info
$stmt = $conn->prepare("SELECT group_name FROM group_chats WHERE id = ?");
$stmt->bind_param("i", $groupId);
$stmt->execute();
$groupResult = $stmt->get_result();
$group = $groupResult->fetch_assoc();

if (!$group) {
    echo "<p class='text-red-500 text-center mt-4'>Group chat does not exist.</p>";
    return;
}

// 💬 Fetch messages
$msgStmt = $conn->prepare("
    SELECT gm.sender_id, gm.message, gm.time_sent, u.name AS sender_name
    FROM group_messages gm
    JOIN users u ON gm.sender_id = u.id
    WHERE gm.group_id = ?
    ORDER BY gm.time_sent ASC
");
$msgStmt->bind_param("i", $groupId);
$msgStmt->execute();
$msgResult = $msgStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Group Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">

  <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md border border-gray-300">
    <h2 class="text-xl font-bold mb-4 text-center text-blue-800">Group Chat: <?= htmlspecialchars($group['group_name']) ?></h2>

    <!-- 📨 Message Display -->
    <div class="border rounded p-4 h-[400px] overflow-y-auto bg-gray-50 mb-4">
      <?php while ($row = $msgResult->fetch_assoc()): ?>
        <?php $isOwnMessage = $row['sender_id'] == $userId; ?>
        <div class="mb-3 <?= $isOwnMessage ? 'text-right' : 'text-left' ?>">
          <div class="inline-block <?= $isOwnMessage ? 'ml-auto ' : 'bg-white' ?> rounded p-2 max-w-[75%] shadow">
            <span class="font-semibold text-blue-700 block"><?= htmlspecialchars($row['sender_name']) ?>:</span>
            <div class="text-sm"><?= nl2br(htmlspecialchars($row['message'])) ?></div>
            <div class="text-xs text-gray-500 mt-1"><?= date("M d, h:i A", strtotime($row['time_sent'])) ?></div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- ✍️ Send Message Form -->
    <form method="POST" action="send_group_message.php" class="flex flex-col sm:flex-row gap-2">
      <input type="hidden" name="group_id" value="<?= htmlspecialchars($groupId) ?>">
      <input type="text" name="message"
             class="flex-1 border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-green-400"
             placeholder="Type your message..." required>
      <button type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        Send
      </button>
    </form>
  </div>

</body>
</html>
