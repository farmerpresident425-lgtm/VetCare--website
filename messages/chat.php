<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['user_id'])) {
    exit('Invalid access');
}

$admin_id = $_SESSION['user_id'];
$resident_id = (int) $_GET['user_id'];

// Fetch name
$res = $conn->prepare("SELECT name FROM users WHERE id = ?");
$res->bind_param("i", $resident_id);
$res->execute();
$res->bind_result($resident_name);
$res->fetch();
$res->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Chat with <?= htmlspecialchars($resident_name) ?></title>
<style>
  body { font-family: sans-serif; }
  #chatBox { height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; }
  .msg-admin { text-align: right; color: white; background: blue; padding: 5px; margin: 5px; border-radius: 5px; }
  .msg-res { text-align: left; color: black; background: #eee; padding: 5px; margin: 5px; border-radius: 5px; }
</style>
</head>
<body>
<h2>Chat with: <?= htmlspecialchars($resident_name) ?></h2>
<div id="chatBox"></div>
<form id="chatForm">
  <input type="hidden" name="receiver_id" value="<?= $resident_id ?>">
  <input type="text" name="message" id="messageInput" placeholder="Type a message…" autocomplete="off" required>
  <button type="submit">Send</button>
</form>

<script>
const adminId = <?= $admin_id ?>;
const receiverId = <?= $resident_id ?>;
const chatBox = document.getElementById('chatBox');
const form = document.getElementById('chatForm');
const msgInput = document.getElementById('messageInput');

function fetchMessages() {
  fetch(`fetch_messages.php?receiver_id=${receiverId}`)
    .then(r => r.json())
    .then(data => {
      chatBox.innerHTML = data.map(m => {
        const cls = m.sender_id == adminId ? 'msg-admin' : 'msg-res';
        return `<div class="${cls}">${m.message}</div>`;
      }).join('');
      chatBox.scrollTop = chatBox.scrollHeight;
    });
}

form.onsubmit = e => {
  e.preventDefault();
  const formData = new FormData(form);
  fetch('send_message.php', { method: 'POST', body: formData })
    .then(() => {
      msgInput.value = '';
      fetchMessages();
    });
};

setInterval(fetchMessages, 2000);
fetchMessages();
</script>
</body>
</html>
