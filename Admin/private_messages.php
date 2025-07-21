<?php
include '../config/db.php';

$adminId = $_SESSION['user_id'] ?? 0;
$chatWith = isset($_GET['chat_with']) ? intval($_GET['chat_with']) : null;

if ($chatWith):
    // Show private chat
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $chatWith);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $userName = $user ? $user['name'] : 'Unknown';

    // Fetch messages
    $messages = $conn->prepare("
        SELECT sender_id, message, time_stamp 
        FROM messages 
        WHERE 
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?) 
        ORDER BY time_stamp ASC
    ");
    $messages->bind_param("iiii", $adminId, $chatWith, $chatWith, $adminId);
    $messages->execute();
    $msgResult = $messages->get_result();
?>

<h2 class="text-lg sm:text-xl font-bold mb-4">Chat with <?= htmlspecialchars($userName) ?></h2>

<!-- 🧾 Chat Container -->
<div class="border rounded p-4 h-[400px] overflow-y-auto bg-gray-50 mb-4 scroll-smooth" id="chatBox">
    <?php while ($msg = $msgResult->fetch_assoc()): ?>
        <?php if ($msg['sender_id'] == $adminId): ?>
            <!-- Admin Message -->
            <div class="text-right mb-3">
                <div class="inline-block bg-blue-600 text-white px-4 py-2 rounded-l-xl rounded-tr-xl max-w-[85%] sm:max-w-[70%] break-words">
                    <?= htmlspecialchars($msg['message']) ?>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <?= date("M d, h:i A", strtotime($msg['time_stamp'])) ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Other Message -->
            <div class="text-left mb-3">
                <div class="inline-block bg-gray-200 text-black px-4 py-2 rounded-r-xl rounded-tl-xl max-w-[85%] sm:max-w-[70%] break-words">
                    <?= htmlspecialchars($msg['message']) ?>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <?= date("M d, h:i A", strtotime($msg['time_stamp'])) ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
</div>

<!-- 📤 Send Message -->
<form method="POST" action="send_message.php" class="flex flex-col sm:flex-row gap-2">
    <input type="hidden" name="receiver_id" value="<?= $chatWith ?>">
    <input type="text" name="message" class="flex-1 border rounded p-2 text-sm sm:text-base" placeholder="Type your message..." required>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send</button>
</form>

<script>
    // Auto-scroll to bottom
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php else: 
    // Show user list
    $residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");
    $vets = $conn->query("SELECT id, name FROM users WHERE role = 'veterinarian' AND status = 'approved'");
?>

<!-- 👥 Residents -->
<h3 class="text-lg font-semibold mb-2">Residents</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <?php while ($row = $residents->fetch_assoc()): ?>
        <?php
        $isActive = ($chatWith && $chatWith == $row['id']);
        $highlight = $isActive ? 'border-blue-500 ring-2 ring-blue-300 bg-blue-50' : 'border border-gray-200';
        $textClass = $isActive ? 'text-blue-800 font-semibold' : '';
        ?>
        <div class="flex items-center justify-between bg-white <?= $highlight ?> p-4 rounded shadow transition">
            <span class="<?= $textClass ?>"><?= htmlspecialchars($row['name']) ?></span>
            <a href="admin.php?section=private_messages&chat_with=<?= $row['id'] ?>" class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">Chat</a>
        </div>
    <?php endwhile; ?>
</div>

<!-- 🩺 Veterinarians -->
<h3 class="text-lg font-semibold mb-2">Veterinarians</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <?php while ($row = $vets->fetch_assoc()): ?>
        <?php
        $isActive = ($chatWith && $chatWith == $row['id']);
        $highlight = $isActive ? 'border-indigo-500 ring-2 ring-indigo-300 bg-indigo-50' : 'border border-gray-200';
        $textClass = $isActive ? 'text-indigo-800 font-semibold' : '';
        ?>
        <div class="flex items-center justify-between bg-white <?= $highlight ?> p-4 rounded shadow transition">
            <span class="<?= $textClass ?>"><?= htmlspecialchars($row['name']) ?></span>
            <a href="admin.php?section=private_messages&chat_with=<?= $row['id'] ?>" class="bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">Chat</a>
        </div>
    <?php endwhile; ?>
</div>

<?php endif; ?>
