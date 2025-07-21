<?php
include '../config/db.php';


$userId = $_SESSION['user_id'] ?? null;

// Today's appointments
$today = date('Y-m-d');
$apptTodayStmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
$apptTodayStmt->bind_param("s", $today);
$apptTodayStmt->execute();
$apptTodayStmt->bind_result($apptTodayCount);
$apptTodayStmt->fetch();
$apptTodayStmt->close();

// Pending appointments
$apptPendingStmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$apptPendingStmt->execute();
$apptPendingStmt->bind_result($apptPendingCount);
$apptPendingStmt->fetch();
$apptPendingStmt->close();

// Total animal records
$recordStmt = $conn->prepare("SELECT COUNT(*) FROM animal_health_records");
$recordStmt->execute();
$recordStmt->bind_result($recordCount);
$recordStmt->fetch();
$recordStmt->close();

// Unread messages
$newMessagesStmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$newMessagesStmt->bind_param("i", $userId);
$newMessagesStmt->execute();
$newMessagesStmt->bind_result($newMessages);
$newMessagesStmt->fetch();
$newMessagesStmt->close();

// Get senders of unread messages
$unreadSenders = [];
$sendersStmt = $conn->prepare("
  SELECT users.name, COUNT(messages.id) AS unread_count
  FROM messages
  JOIN users ON messages.sender_id = users.id
  WHERE messages.receiver_id = ? AND messages.is_read = 0
  GROUP BY users.id
");
$sendersStmt->bind_param("i", $userId);
$sendersStmt->execute();
$result = $sendersStmt->get_result();
while ($row = $result->fetch_assoc()) {
    $unreadSenders[] = [
        'name' => $row['name'],
        'unread_count' => $row['unread_count']
    ];
}
$sendersStmt->close();
$today = date('Y-m-d');

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date = ? AND status = 'pending'");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$apptTodayCount = $data['total'];
?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

  <!-- Appointments Today -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold hover:bg-green-200">Appointments Today</h3>
      <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded-full">Total</span>
    </div>
    <p class="text-4xl font-extrabold text-green-900"><?= $apptTodayCount ?></p>
    <p class="mt-2 text-sm text-green-700">Scheduled for <?= date('F j, Y') ?></p>
  </div>

  <!-- Pending Appointments -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-gray-800">Pending Appointments</h3>
      <span class="text-xs bg-gray-300 text-gray-800 px-2 py-1 rounded-full">Total</span>
    </div>
    <p class="text-4xl font-extrabold text-gray-900"><?= $apptPendingCount ?></p>
    <p class="mt-2 text-sm text-gray-700">Awaiting approval</p>
  </div>

  <!-- Animal Records -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-indigo-800">Animal Records</h3>
      <span class="text-xs bg-indigo-200 text-indigo-800 px-2 py-1 rounded-full">Total</span>
    </div>
    <p class="text-4xl font-extrabold text-indigo-900"><?= $recordCount ?></p>
    <p class="mt-2 text-sm text-indigo-700">Health records stored</p>
  </div>

  <!-- Unread Messages -->
  <div class="relative p-6 rounded-xl shadow-md bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-yellow-800">New Messages</h3>
      <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full">Unread</span>
    </div>
    <p class="text-4xl font-extrabold text-yellow-900"><?= $newMessages ?></p>
    <p class="mt-2 text-sm text-yellow-700">Unread messages waiting for reply</p>

</div>

