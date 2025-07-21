<?php
require('../config/db.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'vet', 'resident'])) {
    exit("Access denied.");
}

$myId = $_SESSION['user_id'];
$otherId = $_GET['user_id'] ?? 0;

if (!$otherId || $otherId == $myId) {
    echo "<p class='text-red-500 px-4 py-2'>Invalid user.</p>";
    return;
}

// Fetch user info
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
$stmt->bind_param("i", $otherId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "<p class='text-red-500 px-4 py-2'>User not found.</p>";
    return;
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    $insert = $conn->prepare("INSERT INTO private_messages (sender_id, receiver_id, message, time_sent) VALUES (?, ?, ?, NOW())");
    $insert->bind_param("iis", $myId, $otherId, $message);
    $insert->execute();
}
?>

<div class="max-w-3xl mx-auto bg-white p-4 sm:p-6 rounded-xl shadow mt-6">

    <h2 class="text-lg sm:text-xl font-bold mb-4">💬 Chat with <?= htmlspecialchars($user['name']) ?></h2>

    <!-- Chat Messages Box -->
    <div id="chatBox" class="border rounded bg-gray-50 p-4 h-[400px] overflow-y-auto mb-4 scroll-smooth">
        <?php
        $msgQuery = $conn->prepare("
            SELECT pm.*, u.name AS sender_name, u.role
            FROM private_messages pm
            JOIN users u ON pm.sender_id = u.id
            WHERE (pm.sender_id = ? AND pm.receiver_id = ?)
               OR (pm.sender_id = ? AND pm.receiver_id = ?)
            ORDER BY time_sent ASC
        ");
        $msgQuery->bind_param("iiii", $myId, $otherId, $otherId, $myId);
        $msgQuery->execute();
        $messages = $msgQuery->get_result();

        while ($msg = $messages->fetch_assoc()):
            $isSender = ($msg['sender_id'] == $myId);
            $alignment = $isSender ? 'text-right' : 'text-left';
            $bubbleColor = $isSender ? 'bg-blue-100' : 'bg-gray-200';

            $nameColor = match ($msg['role']) {
                'admin' => 'text-blue-700',
                'vet' => 'text-green-700',
                'resident' => 'text-purple-700',
                'president' => 'text-red-700',
                default => 'text-gray-700',
            };
        ?>
        <div class="mb-3 <?= $alignment ?>">
            <div class="inline-block <?= $bubbleColor ?> p-3 rounded-xl max-w-[85%] sm:max-w-[70%] <?= $isSender ? 'ml-auto' : '' ?>">
                <div class="font-semibold <?= $nameColor ?> text-sm sm:text-base">
                    <?= htmlspecialchars($msg['sender_name']) ?>
                </div>
                <div class="text-sm sm:text-base break-words"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <div class="text-xs text-gray-500 mt-1">
                    <?= date("M d, h:i A", strtotime($msg['time_sent'])) ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Message Send Form -->
    <form method="POST" class="flex flex-col sm:flex-row gap-2">
        <input 
            type="text" 
            name="message" 
            class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm sm:text-base focus:outline-none focus:ring focus:ring-blue-200" 
            placeholder="Type a message..." 
            required>
        <button 
            type="submit" 
            class="bg-green-600 text-white px-4 py-2 rounded text-sm sm:text-base hover:bg-green-700 transition">
            Send
        </button>
    </form>
</div>

<!-- Auto-scroll script -->
<script>
    const chatBox = document.getElementById("chatBox");
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
