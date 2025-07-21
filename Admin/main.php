<?php
session_start();
require '../config/db.php';

// 🔐 Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$adminId = $_SESSION['user_id'];

// ✅ Handle Admin Profile Upload
if (isset($_POST['upload_profile']) && isset($_FILES['new_profile'])) {
    $file = $_FILES['new_profile'];
    $fileName = basename($file['name']);
    $targetDir = '../uploads/profile_pics/';
    $finalFileName = time() . "_" . $fileName;
    $targetFile = $targetDir . $finalFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $finalFileName, $adminId);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<script>alert('❌ Failed to upload profile picture.');</script>";
    }
}

// 🧠 Admin Info
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$fullName = $user['name'] ?? 'Admin';
$profilePic = $user['profile_pic'] ? '../uploads/profile_pics/' . $user['profile_pic'] : 'https://i.pravatar.cc/100?u=' . $adminId;

// 🔢 Unread Private Messages (not replied to)
$unreadSql = "
    SELECT COUNT(*) AS total 
    FROM (
        SELECT m.sender_id
        FROM messages m
        WHERE m.receiver_id = $adminId
          AND m.is_group = 0
          AND m.is_read = 0
          AND NOT EXISTS (
              SELECT 1 
              FROM messages r
              WHERE r.sender_id = $adminId 
                AND r.receiver_id = m.sender_id
                AND r.created_at > m.created_at
          )
        GROUP BY m.sender_id
    ) AS filtered
";
$newMessagesResult = $conn->query($unreadSql);
$newMessages = $newMessagesResult->fetch_assoc()['total'] ?? 0;

// 🔀 Section Routing
$section = $_GET['section'] ?? 'overview';
$groupId = $_GET['group_id'] ?? null;
$allowedSections = ['overview', 'approve_users', 'group_chat', 'messages', 'profile'];

$sectionFile = ($section === 'group_chat' && $groupId)
    ? 'group_chats_admin_view.php'
    : (in_array($section, $allowedSections) ? "../Admin/{$section}.php" : "../Admin/overview.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Main Container -->
<div class="flex flex-col md:flex-row min-h-screen">

  <!-- Sidebar -->
  <aside class="w-full md:w-72 bg-gradient-to-b from-[#e0f2f1] to-[#b2dfdb] text-gray-900 md:h-screen shadow-lg md:sticky top-0 flex flex-col justify-between z-10">
    <div class="p-6">
      
      <!-- ✅ Profile Picture & Name (Auto Upload on Click) -->
      <form id="adminProfileForm" method="POST" enctype="multipart/form-data" class="flex flex-col items-center mb-6 relative">
        <label for="adminUpload" class="cursor-pointer">
          <img src="<?= $profilePic ?>?v=<?= time() ?>" alt="Profile"
               class="w-24 h-24 rounded-full border-4 border-teal-300 shadow object-cover hover:opacity-90 transition" />
        </label>
        <input type="file" name="new_profile" id="adminUpload" accept="image/*" class="hidden"
               onchange="document.getElementById('adminProfileForm').submit();">
        <input type="hidden" name="upload_profile" value="1">
        <p class="mt-3 text-lg font-semibold text-gray-800 text-center"><?= htmlspecialchars($fullName) ?></p>
      </form>

      <!-- Navigation -->
      <nav class="space-y-2 mt-4 text-sm font-medium">
        <a href="?section=overview" class="block px-4 py-2 rounded transition <?= $section === 'overview' ? 'bg-teal-300 font-semibold' : 'hover:bg-teal-200' ?>">Home</a>
        <a href="?section=approve_users" class="block px-4 py-2 rounded transition <?= $section === 'approve_users' ? 'bg-teal-300 font-semibold' : 'hover:bg-teal-200' ?>">Approve Users</a>
        <a href="?section=messages" class="block px-4 py-2 rounded transition <?= $section === 'messages' ? 'bg-teal-300 font-semibold' : 'hover:bg-teal-200' ?>">
          Messages
          <?php if ($newMessages > 0): ?>
            <span class="ml-2 inline-block bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $newMessages ?></span>
          <?php endif; ?>
        </a>
      </nav>
    </div>

    <!-- Logout -->
    <div class="p-6 border-t border-teal-200 text-left">
      <a href="../auth/logout.php"
         class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium transition">
        Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 bg-gray-50 p-4 md:p-8 overflow-auto">
    <div id="content">
      <?php include $sectionFile; ?>
    </div>
  </main>
</div>

<script>
  lucide.createIcons();
</script>

</body>
</html>
