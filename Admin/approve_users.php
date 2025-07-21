<?php
require '../config/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Handle Approve, Reject, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $deleteUserId = $_POST['delete_user_id'] ?? null;
        if ($deleteUserId) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $deleteUserId);
            $stmt->execute();
        }
    }

    header("Location: main.php?section=approve_users");
    exit;
}

// Fetch users
$pendingUsers = $conn->query("SELECT * FROM users WHERE status = 'pending'");
$approvedUsers = $conn->query("SELECT * FROM users WHERE status = 'approved'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approve Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    #contextMenu { width: 140px; }
  </style>
</head>
<body class="bg-blue-50 p-4 sm:p-8 font-sans text-black">

<div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-md border border-blue-100 p-4 sm:p-8">

  <!-- ✅ Pending Users -->
  <h1 class="text-2xl sm:text-3xl font-semibold mb-4 sm:mb-6 border-b border-blue-200 pb-2">Pending User Approvals</h1>

  <?php if ($pendingUsers->num_rows > 0): ?>
  <!-- 🖥️ Desktop Table -->
  <div class="hidden md:block overflow-x-auto mb-10">
    <table class="min-w-full divide-y divide-blue-200 text-sm">
      <thead class="bg-blue-100 text-black uppercase font-semibold text-xs tracking-wider">
        <tr>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2 text-left">Username</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Birthdate</th>
          <th class="px-4 py-2 text-left">Address</th>
          <th class="px-4 py-2 text-left">Purok</th>
          <th class="px-4 py-2 text-left">Role</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-blue-100 text-black">
        <?php while ($user = $pendingUsers->fetch_assoc()): ?>
        <tr class="hover:bg-blue-50">
          <td class="px-4 py-2"><?= htmlspecialchars($user['name']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['birth_date']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['address']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['purok']) ?></td>
          <td class="px-4 py-2 capitalize"><?= htmlspecialchars($user['role']) ?></td>
          <td class="px-4 py-2 text-center">
            <form method="POST" class="flex flex-col sm:flex-row justify-center items-center gap-2">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <button type="submit" name="action" value="approve"
                onclick="return confirm('Approve this user?')"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-md text-sm w-full sm:w-auto">
                Approve
              </button>
              <button type="submit" name="action" value="reject"
                onclick="return confirm('Reject this user?')"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-md text-sm w-full sm:w-auto">
                Reject
              </button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <p class="text-xs text-gray-400 italic mt-2">Swipe left/right to view more columns on mobile.</p>
  </div>

  <!-- 📱 Mobile Card View -->
  <div class="md:hidden space-y-4 mb-10">
    <?php $pendingUsers->data_seek(0); while ($user = $pendingUsers->fetch_assoc()): ?>
    <div class="approved-card bg-white p-4 rounded-lg shadow border border-blue-200">

      <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
      <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p><strong>Birthdate:</strong> <?= htmlspecialchars($user['birth_date']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
      <p><strong>Purok:</strong> <?= htmlspecialchars($user['purok']) ?></p>
      <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>

      <form method="POST" class="flex justify-center gap-2 mt-3">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <button type="submit" name="action" value="approve"
          onclick="return confirm('Approve this user?')"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded text-sm">
          Approve
        </button>
        <button type="submit" name="action" value="reject"
          onclick="return confirm('Reject this user?')"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded text-sm">
          Reject
        </button>
      </form>
    </div>
    <?php endwhile; ?>
  </div>
  <?php else: ?>
    <p class="text-black italic mb-10">There are no pending users at the moment.</p>
  <?php endif; ?>

  <!-- ✅ Approved Users -->
  <h2 class="text-xl sm:text-2xl font-semibold mb-2 border-b border-blue-200 pb-2">Approved Users</h2>
  <input type="text" id="searchInput" placeholder="Search by name..." class="mb-6 px-4 py-2 border border-blue-300 rounded-md w-full max-w-xs text-black focus:outline-none focus:ring-2 focus:ring-blue-500">

  <?php if ($approvedUsers->num_rows > 0): ?>
  <!-- 🖥️ Desktop Table -->
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full divide-y divide-blue-200 text-sm" id="approvedUsersTable">
      <thead class="bg-blue-100 text-black uppercase text-xs font-semibold tracking-wider">
        <tr>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2 text-left">Username</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Birthdate</th>
          <th class="px-4 py-2 text-left">Address</th>
          <th class="px-4 py-2 text-left">Purok</th>
          <th class="px-4 py-2 text-left">Role</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-blue-50 text-black">
        <?php while ($user = $approvedUsers->fetch_assoc()): ?>
        <tr class="hover:bg-blue-50" data-userid="<?= $user['id'] ?>">
          <td class="px-4 py-2"><?= htmlspecialchars($user['name']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['username']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['birth_date']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['address']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($user['purok']) ?></td>
          <td class="px-4 py-2 capitalize"><?= htmlspecialchars($user['role']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- 📱 Mobile Card View -->
<div class="md:hidden space-y-4 mt-6">
  <?php $approvedUsers->data_seek(0); while ($user = $approvedUsers->fetch_assoc()): ?>
  <div class="approved-card bg-white p-4 rounded-lg shadow border border-blue-200">
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Birthdate:</strong> <?= htmlspecialchars($user['birth_date']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
    <p><strong>Purok:</strong> <?= htmlspecialchars($user['purok']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>

    <form method="POST" class="flex justify-center gap-3 mt-3">
      <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
      <button type="submit" name="action" value="delete"
              onclick="return confirm('Delete this user?')"
              class="bg-red-600 hover:bg-red-700 text-white px-4 py-1 rounded text-sm">
        Delete
      </button>
    </form>
  </div>
  <?php endwhile; ?>
</div>


<!-- ✅ JavaScript -->
<script>
document.getElementById('searchInput').addEventListener('input', function () {
  const search = this.value.toLowerCase();

  // ✅ Filter Desktop Table Rows
  const rows = document.querySelectorAll('#approvedUsersTable tbody tr');
  rows.forEach(row => {
    const name = row.cells[0].textContent.toLowerCase();
    row.style.display = name.includes(search) ? '' : 'none';
  });

  // ✅ Filter Mobile Card View
  const cards = document.querySelectorAll('.approved-card');
  cards.forEach(card => {
    const nameParagraph = Array.from(card.querySelectorAll('p')).find(p =>
      p.textContent.toLowerCase().startsWith('name:')
    );

    if (nameParagraph) {
      const nameText = nameParagraph.textContent.toLowerCase();
      card.style.display = nameText.includes(search) ? '' : 'none';
    } else {
      card.style.display = 'none'; // hide if name not found
    }
  });
});

</script>



<?php endif; ?> <!-- ✅ Sakto ra ni diri -->
</div>

</body>
</html>

