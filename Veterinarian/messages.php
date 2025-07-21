<?php

include '../config/db.php';

$userId = $_SESSION['user_id'] ?? 0;
$currentRole = $_SESSION['role'] ?? '';

$chatWith = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : null;

// 🔍 Get unread message sender IDs
$unreadSenders = [];
$unreadQuery = $conn->prepare("SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadQuery->bind_param("i", $userId);
$unreadQuery->execute();
$unreadResult = $unreadQuery->get_result();
while ($row = $unreadResult->fetch_assoc()) {
    $unreadSenders[] = $row['sender_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Veterinarian Chat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-4 text-black">

<?php if ($chatWith): ?>
    <?php
    // 🧠 Get target user name
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $chatWith);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    $targetName = $target ? $target['name'] : 'Unknown';

    // ✅ Mark messages as read
    $markRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $markRead->bind_param("ii", $chatWith, $userId);
    $markRead->execute();

    // 📨 Fetch messages
    $messages = $conn->prepare("
        SELECT id, sender_id, message, created_at AS time_stamp
        FROM messages 
        WHERE 
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?) 
        ORDER BY time_stamp ASC
    ");
    $messages->bind_param("iiii", $userId, $chatWith, $chatWith, $userId);
    $messages->execute();
    $msgResult = $messages->get_result();
    ?>

    <h2 class="text-xl font-bold mb-4 text-blue-800">Chat with <?= htmlspecialchars($targetName) ?></h2>

    <div class="bg-white rounded shadow-md p-4 mb-4 h-[30rem] overflow-y-auto relative">
        <?php while ($msg = $msgResult->fetch_assoc()): ?>
            <?php if ($msg['sender_id'] == $userId): ?>
                <div class="text-right mb-3">
                    <div 
                        class="inline-block text-black px-4 py-2 rounded-l-xl rounded-tr-xl max-w-[80%] break-words"
                        oncontextmenu="showContextMenu(event, <?= htmlspecialchars(json_encode($msg['message'])) ?>, <?= $msg['id'] ?>)"
                    >
                        <?= htmlspecialchars($msg['message']) ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1"><?= date("M d, h:i A", strtotime($msg['time_stamp'])) ?></div>
                </div>
            <?php else: ?>
                <div class="text-left mb-3">
                    <div class="inline-block text-black px-4 py-2 rounded-r-xl rounded-tl-xl max-w-[80%] break-words">
                        <?= htmlspecialchars($msg['message']) ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1"><?= date("M d, h:i A", strtotime($msg['time_stamp'])) ?></div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>

    <!-- Context Menu -->
    <div id="contextMenu" class="hidden absolute bg-white border rounded shadow-md z-50 text-sm">
        <ul>
            <li onclick="editMessage()" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Edit</li>
            <li onclick="deleteMessage()" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Delete</li>
        </ul>
    </div>

    <!-- Send Message -->
    <form method="POST" action="send_message.php" class="flex gap-2 items-end">
        <input type="hidden" name="receiver_id" value="<?= $chatWith ?>">
        <textarea name="message" rows="2" class="flex-1 border rounded-md p-2 resize-none" placeholder="Type your message..." required></textarea>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Send</button>
    </form>

<?php else: ?>
    <?php
    $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE role = 'resident' AND status = 'approved'");
    $stmt->execute();
    $usersResult = $stmt->get_result();
    ?>

    

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <?php while ($row = $usersResult->fetch_assoc()): ?>
            <?php
            $isActiveChat = ($chatWith == $row['id']);
            $hasUnread = in_array($row['id'], $unreadSenders);

            $highlight = $isActiveChat
                ? 'border-blue-500 ring-2 ring-blue-300 bg-blue-50'
                : ($hasUnread ? 'border-amber-400 ring-2 ring-amber-200 bg-amber-50' : 'border');

            $nameText = $isActiveChat 
                ? 'text-blue-800' 
                : ($hasUnread ? 'text-amber-700' : '');
            ?>
            <div class="flex items-center justify-between <?= $highlight ?> p-4 rounded shadow transition">
                <div>
                    <p class="font-semibold <?= $nameText ?>">
                        <?= htmlspecialchars($row['name']) ?>
                    </p>
                    <p class="text-sm text-gray-500"><?= ucfirst($row['role']) ?></p>
                </div>
                <form action="main.php" method="get">
                    <input type="hidden" name="section" value="messages">
                    <input type="hidden" name="chat_with" value="<?= $row['id'] ?>">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded">Chat</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    

    <form action="main.php" method="get">
        <input type="hidden" name="section" value="group_chat">
        <button class="bg-green-600 text-white px-6 py-2 rounded">Go to Group Chat</button>
    </form>
<?php endif; ?>


<!-- JS for Edit/Delete Context Menu -->
<script>
let selectedMessageId = null;
let selectedMessageContent = null;

function showContextMenu(e, message, messageId) {
    e.preventDefault();
    selectedMessageId = messageId;
    selectedMessageContent = message;

    const menu = document.getElementById("contextMenu");
    menu.style.top = `${e.pageY}px`;
    menu.style.left = `${e.pageX}px`;
    menu.classList.remove("hidden");

    document.addEventListener("click", hideContextMenu);
}

function hideContextMenu() {
    const menu = document.getElementById("contextMenu");
    if (menu) menu.classList.add("hidden");
    document.removeEventListener("click", hideContextMenu);
}

function editMessage() {
    const newMsg = prompt("Edit your message:", selectedMessageContent);
    if (newMsg !== null && newMsg.trim() !== "") {
        fetch('edit_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: selectedMessageId, new_message: newMsg })
        }).then(() => location.reload());
    }
}

function deleteMessage() {
    if (confirm("Are you sure you want to delete this message?")) {
        fetch('delete_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: selectedMessageId })
        }).then(() => location.reload());
    }
}
</script>

</body>
</html>
