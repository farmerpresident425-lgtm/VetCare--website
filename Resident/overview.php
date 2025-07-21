<?php
include '../config/db.php';


$userId = $_SESSION['user_id'] ?? null;

$totalAppointments = 0;
$upcoming = 0;
$completed = 0;
$cancelled = 0;
$newMessages = 0;
$unreadSenders = [];

if ($userId) {
    // Appointments summary
    $stmt = $conn->prepare("SELECT status, COUNT(*) AS count FROM appointments WHERE resident_id = ? GROUP BY status");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        $count = $row['count'];
        $totalAppointments += $count;

        if ($status === 'approved') $upcoming = $count;
        elseif ($status === 'completed') $completed = $count;
        elseif ($status === 'cancelled') $cancelled = $count;
    }

    $stmt->close();

    // 🔁 Get latest messages per sender, unread & unreplied only
    $sql = "
        SELECT m.sender_id, COUNT(*) AS unread_count
        FROM messages m
        JOIN (
            SELECT sender_id, MAX(created_at) AS last_msg_time
            FROM messages
            WHERE receiver_id = ? AND is_group = 0
            GROUP BY sender_id
        ) AS latest_msg ON latest_msg.sender_id = m.sender_id AND m.created_at = latest_msg.last_msg_time
        WHERE m.receiver_id = ? AND m.is_read = 0 AND m.sender_id != ?
        GROUP BY m.sender_id
    ";

    $msgStmt = $conn->prepare($sql);
    $msgStmt->bind_param("iii", $userId, $userId, $userId);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result();

    while ($row = $msgResult->fetch_assoc()) {
        $unreadSenders[] = $row;
        $newMessages += $row['unread_count'];
    }

    $msgStmt->close();

}

// Include database connection and session checks above this
$vetId = $_SESSION['user_id'];

// Get unread messages count and sender list
$unreadSendersResult = $conn->query("
    SELECT users.id, users.name, COUNT(messages.id) AS unread_count
    FROM messages
    JOIN users ON messages.sender_id = users.id
    WHERE messages.receiver_id = $vetId AND messages.is_read = 0
    GROUP BY users.id
");

if ($unreadSendersResult->num_rows > 0): ?>
    
  
    
<?php endif; ?>


<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

  <!-- 🗓 Total Appointments -->
  <div class="w-full sm:w-72 p-6 rounded-xl shadow-lg bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 transition hover:shadow-xl">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg sm:text-xl font-semibold text-blue-800">Request Appointments</h3>
      <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded-full font-medium">Total</span>
    </div>
    <p class="text-3xl sm:text-4xl font-extrabold text-blue-900"><?= $totalAppointments ?></p>
  </div>

  <!-- ✅ Approved -->
  <div class="w-full sm:w-72 p-6 rounded-xl shadow-lg bg-gradient-to-br from-green-50 to-green-100 border border-green-200 transition hover:shadow-xl">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg sm:text-xl font-semibold text-green-800">Approved Appointments</h3>
      <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded-full font-medium">Total</span>
    </div>
    <p class="text-3xl sm:text-4xl font-extrabold text-green-900"><?= $upcoming ?></p>
  </div>

  <!-- ❌ Cancelled -->
  <div class="w-full sm:w-72 p-6 rounded-xl shadow-lg bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200 transition hover:shadow-xl">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg sm:text-xl font-semibold text-teal-800">Cancelled Appointments</h3>
      <span class="text-xs bg-teal-200 text-teal-800 px-2 py-1 rounded-full font-medium">Total</span>
    </div>
    <p class="text-3xl sm:text-4xl font-extrabold text-teal-900"><?= $cancelled ?></p>
  </div>

  <!-- ✉️ New Messages -->
  <div class="relative group w-full sm:w-72 p-6 rounded-xl shadow-lg bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 transition hover:shadow-xl">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg sm:text-xl font-semibold text-yellow-800">New Messages</h3>
      <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full font-medium">Unread</span>
    </div>
    <p class="text-3xl sm:text-4xl font-extrabold text-yellow-900"><?= $newMessages ?></p>
    <p class="mt-2 text-sm text-yellow-700">Unread messages waiting for reply</p>

    <?php if (!empty($unreadSenders)): ?>
      <div class="absolute left-0 top-full mt-2 w-full bg-white border border-yellow-300 rounded-xl shadow-lg p-4 hidden group-hover:block z-10">
        <h4 class="text-sm font-semibold text-yellow-800 mb-2">From:</h4>
        <ul class="text-sm text-yellow-700 space-y-1 max-h-40 overflow-auto">
          <?php foreach ($unreadSenders as $sender): 
              $senderId = $sender['sender_id'];
              $senderNameQuery = $conn->query("SELECT name FROM users WHERE id = $senderId");
              $senderName = $senderNameQuery->fetch_assoc()['name'] ?? 'Unknown';
          ?>
            <li class="flex justify-between items-center bg-yellow-50 border border-yellow-200 px-3 py-2 rounded cursor-pointer transition hover:bg-yellow-200" title="Click to view">
              <span><?= htmlspecialchars($senderName) ?></span>
              <span class="font-bold"><?= $sender['unread_count'] ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

</div>
