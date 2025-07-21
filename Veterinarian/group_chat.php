<?php
include '../config/db.php';


$vetId = $_SESSION['user_id'] ?? 0;

// Handle group creation
// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_name'])) {
    $groupName = trim($_POST['group_name']);
    if (!empty($groupName)) {
        $stmt = $conn->prepare("INSERT INTO group_chats (group_name, created_by) VALUES (?, ?)");
        $stmt->bind_param("si", $groupName, $vetId);
        if ($stmt->execute()) {
            $groupId = $stmt->insert_id;

            // ✅ Add vet to group
            $conn->query("INSERT INTO group_members (group_id, user_id) VALUES ($groupId, $vetId)");

            // ✅ Add all approved admins
            $admins = $conn->query("SELECT id FROM users WHERE role = 'admin' AND status = 'approved'");
            while ($admin = $admins->fetch_assoc()) {
                $conn->query("INSERT INTO group_members (group_id, user_id) VALUES ($groupId, {$admin['id']})");
            }

            echo "<p class='text-green-600 mb-4'>✅ Group created successfully!</p>";
        } else {
            echo "<p class='text-red-600 mb-4'>❌ Failed to create group.</p>";
        }
    }
}

// Load the most recent group
$groupQuery = $conn->query("SELECT * FROM group_chats ORDER BY created_at DESC LIMIT 1");
$group = $groupQuery->fetch_assoc();
?>



<?php if (!$group): ?>
  <!-- Group creation form -->
  <form method="POST" class="mb-6">
    <label for="group_name" class="block mb-2 font-semibold">Enter Group Name:</label>
    <input type="text" name="group_name" required class="border p-2 rounded w-full mb-4">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create Group</button>
  </form>

<?php else: ?>

<!-- FLEX CONTAINER -->
<div class="flex flex-col md:flex-row gap-6">

  <!-- LEFT: MESSAGES + INPUT -->
  <div class="flex-1 flex flex-col justify-between">

    <p class="mb-2 text-gray-700">🟢 Active Group: <strong><?= htmlspecialchars($group['group_name']) ?></strong></p>

   <!-- MESSAGE AREA -->
<div class="border rounded bg-gray-50 p-4 h-[400px] overflow-y-auto mb-4">
  <?php
    $msgQuery = $conn->prepare("
        SELECT gm.*, u.name, u.role FROM group_messages gm
        JOIN users u ON gm.sender_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.time_sent ASC
    ");
    $msgQuery->bind_param("i", $group['id']);
    $msgQuery->execute();
    $messages = $msgQuery->get_result();

    while ($msg = $messages->fetch_assoc()):
        $isVet = $msg['sender_id'] == $vetId;
        $alignment = $isVet ? 'text-right' : 'text-left';
        $bubbleColor = $isVet ? 'bg-white' : 'bg-white';

        // Assign name color based on role
        $nameColor = match ($msg['role']) {
            'vet' => 'text-green-700',
            'admin' => 'text-orange-700',
            'resident' => 'text-blue-700',
            default => 'text-black'
        };
  ?>
    <div class="mb-2 <?= $alignment ?>">
      <div class="inline-block <?= $bubbleColor ?> p-3 rounded-lg max-w-[70%] <?= $isVet ? '' : 'ml-auto' ?>">
        <div class="font-semibold <?= $nameColor ?>">
          <?= htmlspecialchars($msg['name']) ?>
        </div>
        <div><?= htmlspecialchars($msg['message']) ?></div>
        <div class="text-xs text-gray-500 mt-1">
          <?= date("M d, h:i A", strtotime($msg['time_sent'])) ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

    <!-- INPUT BOX -->
    <form method="POST" action="send_group_message.php" class="flex gap-2">
      <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
      <input type="text" name="message" class="flex-1 border rounded p-2" placeholder="Type a message..." required>
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Send</button>
    </form>
  </div>

  <!-- RIGHT: ADD MEMBERS -->
  <div class="w-full md:w-[300px]">

    <h3 class="text-lg font-bold mb-2"> Add Members to Group</h3>

    <?php
      $users = $conn->query("
          SELECT u.id, u.name, u.role FROM users u 
          WHERE u.role IN ('resident', 'admin') AND u.status = 'approved'
          ORDER BY u.role, u.name
      ");

      $memberIds = [];
      $res = $conn->prepare("SELECT user_id FROM group_members WHERE group_id = ?");
      $res->bind_param("i", $group['id']);
      $res->execute();
      $resResult = $res->get_result();
      while ($r = $resResult->fetch_assoc()) {
          $memberIds[] = $r['user_id'];
      }
    ?>

    <form method="POST" action="add_group_member.php">
      <input type="hidden" name="group_id" value="<?= $group['id'] ?>">

      <!-- Admins Accordion -->
      <div class="mb-4 border rounded">
        <button type="button" onclick="toggleSection('admins')" class="w-full text-left px-4 py-2 bg-gray-200 font-semibold">
           Admins
        </button>
        <div id="admins" class="p-4 hidden">
          <?php
            $admins = $conn->query("SELECT id, name FROM users WHERE role = 'admin' AND status = 'approved'");
            while ($admin = $admins->fetch_assoc()):
              $checked = in_array($admin['id'], $memberIds) ? 'checked disabled' : '';
          ?>
            <label class="flex items-center gap-2 mb-2">
              <input type="checkbox" name="user_ids[]" value="<?= $admin['id'] ?>" <?= $checked ?>>
              <span><?= htmlspecialchars($admin['name']) ?></span>
            </label>
          <?php endwhile; ?>
        </div>
      </div>

      <!-- Residents Accordion -->
      <div class="mb-4 border rounded">
        <button type="button" onclick="toggleSection('residents')" class="w-full text-left px-4 py-2 bg-gray-200 font-semibold">
           Residents
        </button>
        <div id="residents" class="p-4 hidden">
          <?php
            $residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");
            while ($res = $residents->fetch_assoc()):
              $checked = in_array($res['id'], $memberIds) ? 'checked disabled' : '';
          ?>
            <label class="flex items-center gap-2 mb-2">
              <input type="checkbox" name="user_ids[]" value="<?= $res['id'] ?>" <?= $checked ?>>
              <span><?= htmlspecialchars($res['name']) ?></span>
            </label>
          <?php endwhile; ?>
        </div>
      </div>

      <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Add Selected Members</button>
    </form>
  </div>
</div>

<script>
function toggleSection(id) {
  document.getElementById(id).classList.toggle('hidden');
}
</script>

<?php endif; ?>
