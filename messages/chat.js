let currentReceiverId = null;
let fetchInterval = null;

function openChat(id, name) {
    currentReceiverId = id;
    document.getElementById('receiver_id').value = id;
    document.getElementById('chatWith').textContent = "Chatting with: " + name;
    loadMessages();

    if (fetchInterval) clearInterval(fetchInterval);
    fetchInterval = setInterval(loadMessages, 2000); // Refresh every 2 seconds
}

function loadMessages() {
    if (!currentReceiverId) return;

    fetch(`../messages/fetch_messages.php?receiver_id=${currentReceiverId}`)
        .then(res => res.json())
        .then(data => {
            let chatBox = document.getElementById('chatBox');
            chatBox.innerHTML = '';
            data.forEach(msg => {
                const isAdmin = msg.sender_id == <?= $_SESSION['user_id'] ?>;
                const bubble = document.createElement('div');
                bubble.className = `p-2 my-1 max-w-[70%] rounded ${isAdmin ? 'bg-blue-500 text-white self-end ml-auto' : 'bg-gray-300 text-black'}`;
                bubble.textContent = msg.message;
                chatBox.appendChild(bubble);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        });
}

function sendMessage() {
    const form = document.getElementById('chatForm');
    const formData = new FormData(form);
    fetch('../messages/send_message.php', {
        method: 'POST',
        body: formData
    }).then(() => {
        form.message.value = '';
        loadMessages();
    });
    return false;
}
