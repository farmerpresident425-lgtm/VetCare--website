<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="flex max-w-6xl mx-auto bg-white rounded shadow h-[80vh] overflow-hidden">
        
        <!-- Sidebar: List of Residents -->
        <div class="w-1/4 border-r p-4 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4">Residents</h2>
            <?php while ($r = $residents->fetch_assoc()): ?>
                <button onclick="openChat(<?= $r['id'] ?>, '<?= addslashes($r['name']) ?>')" 
                        class="block w-full text-left p-2 mb-2 bg-gray-200 rounded hover:bg-blue-400 hover:text-white">
                    <?= htmlspecialchars($r['name']) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <!-- Chat Box -->
        <div class="flex-1 flex flex-col">
            <div class="p-4 border-b">
                <h2 id="chatWith" class="text-xl font-bold">Select a resident to chat</h2>
            </div>
            <div id="chatBox" class="flex-1 p-4 overflow-y-auto bg-gray-50"></div>

            <form id="chatForm" class="p-4 border-t flex" onsubmit="return sendMessage()">
                <input type="hidden" id="receiver_id" name="receiver_id">
                <input type="text" id="message" name="message" placeholder="Type a message" class="flex-1 p-2 border rounded" required>
                <button type="submit" class="ml-2 bg-green-500 text-white px-4 py-2 rounded">Send</button>
            </form>
        </div>
    </div>

    <script src="chat.js"></script>
</body>
</html>
