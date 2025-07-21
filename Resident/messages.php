<?php
include '../config/db.php';

$residentId = $_SESSION['user_id'] ?? 0;
$chatWith = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : null;

if ($_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}
?>
<div class="w-full px-4 sm:px-6">
<?php if ($chatWith): ?>

<?php
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $chatWith);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user ? $user['name'] : 'Unknown';

$markRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
$markRead->bind_param("ii", $chatWith, $residentId);
$markRead->execute();

$msg = $conn->prepare("
    SELECT id, sender_id, message, created_at 
    FROM messages 
    WHERE 
        (sender_id = ? AND receiver_id = ?) OR 
        (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$msg->bind_param("iiii", $residentId, $chatWith, $chatWith, $residentId);
$msg->execute();
$msgResult = $msg->get_result();
?>

<h3 class="text-lg font-semibold mb-2">Chat with <?= htmlspecialchars($userName) ?></h3>

<div class="border rounded p-3 h-[400px] overflow-y-auto bg-gray-50 mb-4 relative" id="chatContainer">
    <?php while ($row = $msgResult->fetch_assoc()): ?>
        <?php $isOwn = $row['sender_id'] == $residentId; ?>
        <div class="mb-3 <?= $isOwn ? 'text-right' : 'text-left' ?>">
            <div 
                class="inline-block px-4 py-2 max-w-[80%] sm:max-w-[70%] text-sm rounded shadow 
                       <?= $isOwn ? ' ml-auto rounded-tl-xl rounded-bl-xl rounded-tr-xl' : 'bg-white rounded-tr-xl rounded-br-xl rounded-tl-xl' ?>"
                oncontextmenu="<?= $isOwn ? "showContextMenu(event, " . htmlspecialchars(json_encode($row['message'])) . ", " . $row['id'] . ")" : '' ?>"
            >
                <?= htmlspecialchars($row['message']) ?>
                <div class="text-xs mt-1"><?= date("M d, h:i A", strtotime($row['created_at'])) ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<form method="POST" action="send_message.php" class="flex flex-col sm:flex-row gap-2">
    <input type="hidden" name="receiver_id" value="<?= $chatWith ?>">
    <input type="text" name="message" class="flex-1 border rounded p-2 text-sm" placeholder="Type your message..." required>
    <div class="text-center">
        <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 text-sm rounded">
            Send
        </button>
    </div>
</form>

<?php else: ?>
<?php
$groupCheck = $conn->prepare("SELECT gc.id, gc.group_name FROM group_chats gc JOIN group_members gm ON gc.id = gm.group_id WHERE gm.user_id = ?");
$groupCheck->bind_param("i", $residentId);
$groupCheck->execute();
$groupRes = $groupCheck->get_result();

$chatHistoryStmt = $conn->prepare("SELECT id, name, role FROM users WHERE status = 'approved' AND id != ?");
$chatHistoryStmt->bind_param("i", $residentId);
$chatHistoryStmt->execute();
$historyResult = $chatHistoryStmt->get_result();
?>

<div class="w-full flex flex-col">
    <!-- 🔁 Search Bar -->
    <div class="relative w-full sm:w-[50%] max-w-md mb-4">
        <input type="text" id="userSearch" placeholder="🔍 Search name..." class="w-full px-3 py-2 border border-green-600 rounded text-sm" autocomplete="off">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6" id="userList">
       <?php while ($user = $historyResult->fetch_assoc()): ?>
    <?php
    // Check if this user has unread messages for the current resident
    $unreadStmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM messages 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $unreadStmt->bind_param("ii", $user['id'], $residentId);
    $unreadStmt->execute();
    $unreadResult = $unreadStmt->get_result();
    $unreadData = $unreadResult->fetch_assoc();
    $hasUnread = $unreadData['unread_count'] > 0;
    ?>
    <div class="flex justify-between items-center <?= $hasUnread ? 'bg-red-100' : 'bg-gray-100' ?> px-4 py-2 rounded hover:bg-gray-200">
        <span class="text-base font-semibold <?= $hasUnread ? 'text-red-600' : '' ?>">
            <?= htmlspecialchars($user['name']) ?><br>
            <span class="text-sm <?= $hasUnread ? 'text-red-500' : 'text-gray-600' ?>">
                <?= htmlspecialchars($user['role']) ?>
                <?= $hasUnread ? ' • New message' : '' ?>
            </span>
        </span>
        <form method="get" action="main.php">
            <input type="hidden" name="section" value="messages">
            <input type="hidden" name="chat_with" value="<?= $user['id'] ?>">
            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Chat</button>
        </form>
    </div>
<?php endwhile; ?>


        <!-- 👥 Group Chats (below user list) -->
        <?php
        $groupCheck->execute(); // re-execute
        $groupRes = $groupCheck->get_result();
        ?>
        <?php while ($group = $groupRes->fetch_assoc()): ?>
            <div class="flex justify-between items-center bg-yellow-100 px-4 py-2 rounded hover:bg-yellow-200">
                <span class="text-base font-semibold text-yellow-800">
                    <?= htmlspecialchars($group['group_name']) ?><br>
                    <span class="text-sm text-yellow-700 italic">Group Chat</span>
                </span>
                <form method="get" action="main.php">
                    <input type="hidden" name="section" value="group_chat">
                    <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                    <button type="submit" class="bg-yellow-600 text-white px-3 py-1 text-sm rounded hover:bg-yellow-700">Enter</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
</div>

<!-- Context Menu -->
<div id="contextMenu" class="hidden absolute bg-white border rounded shadow-md z-50 text-sm">
    <ul>
        <li onclick="editMessage()" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Edit</li>
        <li onclick="deleteMessage()" class="px-4 py-2 hover:bg-gray-100 cursor-pointer">Delete</li>
    </ul>
</div>

<!-- JavaScript -->
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
    return false;
}

function hideContextMenu() {
    document.getElementById("contextMenu").classList.add("hidden");
    document.removeEventListener("click", hideContextMenu);
}

function editMessage() {
    const newMsg = prompt("Edit your message:", selectedMessageContent);
    if (newMsg && newMsg.trim() !== "") {
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

document.getElementById('userSearch').addEventListener('input', function () {
    const search = this.value.toLowerCase();
    document.querySelectorAll('#userList > div').forEach(userCard => {
        const name = userCard.textContent.toLowerCase();
        userCard.style.display = name.includes(search) ? 'flex' : 'none';
    });
});
</script>
