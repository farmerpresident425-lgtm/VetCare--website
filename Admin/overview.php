<?php
require '../config/db.php';

// ✅ Approved users
$totalResidents = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'resident' AND status = 'approved'")->fetch_assoc()['total'] ?? 0;
$totalVets      = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'veterinarian' AND status = 'approved'")->fetch_assoc()['total'] ?? 0;
$totalAdmins    = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 'approved'")->fetch_assoc()['total'] ?? 0;

// ⏳ Pending users
$pendingResidents = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'resident' AND status = 'pending'")->fetch_assoc()['total'] ?? 0;
$pendingVets      = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'veterinarian' AND status = 'pending'")->fetch_assoc()['total'] ?? 0;
$pendingAdmins    = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 'pending'")->fetch_assoc()['total'] ?? 0;

// Defaults
$newMessages = $newMessages ?? 0;
$unreadSendersQuery = $unreadSendersQuery ?? false;
?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

  <!-- Residents -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-blue-800">Residents</h3>
      <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded-full">Approved</span>
    </div>
    <p class="text-4xl font-extrabold text-blue-900"><?= $totalResidents ?></p>
    <p class="mt-2 text-sm text-blue-700">Pending: <span class="font-bold"><?= $pendingResidents ?></span></p>
  </div>

  <!-- Veterinarians -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-green-800">Veterinarians</h3>
      <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded-full">Approved</span>
    </div>
    <p class="text-4xl font-extrabold text-green-900"><?= $totalVets ?></p>
    <p class="mt-2 text-sm text-green-700">Pending: <span class="font-bold"><?= $pendingVets ?></span></p>
  </div>

  <!-- Admins -->
  <div class="p-6 rounded-xl shadow-md bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-teal-800">Admins</h3>
      <span class="text-xs bg-teal-200 text-teal-800 px-2 py-1 rounded-full">Approved</span>
    </div>
    <p class="text-4xl font-extrabold text-teal-900"><?= $totalAdmins ?></p>
    <p class="mt-2 text-sm text-teal-700">Pending: <span class="font-bold"><?= $pendingAdmins ?></span></p>
  </div>

  <!-- Unread Messages -->
  <div class="relative p-6 rounded-xl shadow-md bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-xl font-semibold text-yellow-800">New Messages</h3>
      <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full">Unread</span>
    </div>
    <p class="text-4xl font-extrabold text-yellow-900"><?= $newMessages ?></p>
    <p class="mt-2 text-sm text-yellow-700">Unread messages waiting for reply</p>

    <!-- Sender Dropdown -->
    <?php if ($unreadSendersQuery && $unreadSendersQuery->num_rows > 0): ?>
      <button onclick="toggleDropdown()" class="mt-4 w-full bg-yellow-200 text-yellow-800 py-2 rounded font-medium hover:bg-yellow-300 transition">
        Show Senders
      </button>

      <div id="dropdownSenders" class="hidden mt-3 bg-white border border-yellow-300 rounded-xl shadow-lg p-4 max-h-48 overflow-auto">
        <h4 class="text-sm font-semibold text-yellow-800 mb-2">From:</h4>
        <ul class="space-y-1 text-sm text-yellow-700">
          <?php while ($row = $unreadSendersQuery->fetch_assoc()): ?>
            <li class="flex justify-between bg-yellow-50 border border-yellow-200 px-3 py-2 rounded">
              <span><?= htmlspecialchars($row['name']) ?></span>
              <span class="font-bold"><?= $row['unread_count'] ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
function toggleDropdown() {
  const dropdown = document.getElementById("dropdownSenders");
  if (dropdown) dropdown.classList.toggle("hidden");
}
</script>
