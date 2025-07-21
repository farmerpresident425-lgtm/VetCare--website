<?php
session_start();
include '../config/db.php';

$userId = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['role'] ?? '';

if (!$userId || $userRole !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

// ✅ Handle Profile Upload
if (isset($_POST['upload_profile']) && isset($_FILES['new_profile'])) {
    $file = $_FILES['new_profile'];
    $fileName = basename($file['name']);
    $targetDir = '../uploads/profile_pics/';
    $finalFileName = time() . "_" . $fileName;
    $targetFile = $targetDir . $finalFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $finalFileName, $userId);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "<script>alert('❌ Failed to upload profile picture.');</script>";
    }
}

// ✅ Unread messages count
$unreadSql = "
    SELECT COUNT(*) AS total
    FROM (
        SELECT m.sender_id, MAX(m.created_at) AS last_msg_time
        FROM messages m
        WHERE m.receiver_id = $userId AND m.is_group = 0
        GROUP BY m.sender_id
    ) AS last_msgs
    JOIN messages lm 
      ON lm.sender_id = last_msgs.sender_id 
     AND lm.created_at = last_msgs.last_msg_time
    WHERE lm.receiver_id = $userId 
      AND lm.is_read = 0
      AND lm.sender_id != $userId
";
$newMessagesResult = $conn->query($unreadSql);
$newMessages = $newMessagesResult->fetch_assoc()['total'] ?? 0;

// 🔍 Profile info
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$fullName = $user['name'] ?? 'Unknown';
$profilePic = !empty($user['profile_pic']) 
    ? '../uploads/profile_pics/' . $user['profile_pic'] 
    : '../../assets/default-profile.png';

$section = $_GET['section'] ?? 'overview';
$allowed = ['overview', 'register_animals', 'my_animals', 'request_form', 'appointments', 'messages', 'resources', 'group_chat', 'add_animal', 'profile'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Resident Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<!-- 🔵 HEADER -->
<header class="bg-white shadow-md px-6 py-4">
  <div class="flex flex-col md:flex-row items-center justify-between gap-6 w-full">

    <!-- ✅ Profile (with auto upload) -->
    <form id="profileForm" method="POST" enctype="multipart/form-data" class="flex flex-col items-center relative">
      <label for="profileUpload" class="cursor-pointer">
        <img src="<?= htmlspecialchars($profilePic) ?>?v=<?= time() ?>" class="w-24 h-24 rounded-full border-4 border-blue-400 shadow-sm object-cover hover:opacity-90 transition" alt="Profile">
      </label>
      <input type="file" name="new_profile" id="profileUpload" accept="image/*" class="hidden" onchange="document.getElementById('profileForm').submit();">
      <input type="hidden" name="upload_profile" value="1">
      <p class="mt-3 text-lg font-semibold text-gray-900 text-center"><?= htmlspecialchars($fullName) ?></p>
    </form>

    <!-- Navigation (Desktop) -->
    <nav class="hidden md:flex flex-wrap justify-center items-center gap-3 text-center flex-1">
      <a href="?section=overview" class="bg-blue-100 text-blue-900 px-5 py-3 rounded-md hover:bg-blue-200 font-semibold border border-blue-300 transition">Home</a>

      <!-- Register Animals -->
      <div class="relative inline-block">
        <button onclick="toggleMenu('animalDropdown')" class="bg-blue-100 text-blue-900 px-5 py-3 rounded-md hover:bg-blue-200 font-semibold border border-blue-300 transition">
          Register Animals ▼
        </button>
        <div id="animalDropdown" class="hidden absolute mt-2 bg-white border border-gray-300 rounded shadow-md z-50 w-56">
          <a href="?section=add_animal" class="block px-4 py-2 hover:bg-blue-100">Register Animal</a>
          <a href="?section=my_animals" class="block px-4 py-2 hover:bg-blue-100">View Registered Animals</a>
        </div>
      </div>

      <!-- Appointments -->
      <div class="relative inline-block">
        <button onclick="toggleMenu('appointmentDropdown')" class="bg-blue-100 text-blue-900 px-5 py-3 rounded-md hover:bg-blue-200 font-semibold border border-blue-300 transition">
          Appointments ▼
        </button>
        <div id="appointmentDropdown" class="hidden absolute mt-2 bg-white border border-gray-300 rounded shadow-md z-50 w-56">
          <a href="?section=request_form" class="block px-4 py-2 hover:bg-blue-100">Request Appointment</a>
          <a href="?section=appointments" class="block px-4 py-2 hover:bg-blue-100">View Appointments</a>
        </div>
      </div>

      <a href="?section=messages" class="bg-blue-100 text-blue-900 px-5 py-3 rounded-md hover:bg-blue-200 font-semibold border border-blue-300 transition">Messages <?= $newMessages ? "($newMessages)" : '' ?></a>

      <!-- Resources -->
      <div class="relative inline-block">
        <button onclick="toggleMenu('resourcesDropdown')" class="bg-blue-100 text-blue-900 px-5 py-3 rounded-md hover:bg-blue-200 font-semibold border border-blue-300 transition">
          Resources ▼
        </button>
        <div id="resourcesDropdown" class="hidden absolute mt-2 bg-white border border-gray-300 rounded shadow-md z-50 w-44">
          <a href="?section=resources&tab=links" class="block px-4 py-2 hover:bg-blue-100">Links</a>
          <a href="?section=resources&tab=videos" class="block px-4 py-2 hover:bg-blue-100">Videos</a>
          <a href="?section=resources&tab=images" class="block px-4 py-2 hover:bg-blue-100">Images</a>
        </div>
      </div>
    </nav>

    <!-- Mobile Navigation -->
    <div class="md:hidden w-full">
      <button onclick="toggleMenu('mobileNav')" class="bg-blue-600 w-full text-white px-5 py-3 rounded-md font-semibold">☰ Menu</button>
      <div id="mobileNav" class="hidden mt-2 space-y-2">
        <a href="?section=overview" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Home</a>
        <a href="?section=add_animal" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Register Animal</a>
        <a href="?section=my_animals" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Registered Animals</a>
        <a href="?section=request_form" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Request Appointment</a>
        <a href="?section=appointments" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Appointments</a>
        <a href="?section=messages" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Messages <?= $newMessages ? "($newMessages)" : '' ?></a>
        <a href="?section=resources&tab=links" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">Resources</a>
        <a href="?section=profile" class="block bg-white border px-4 py-2 rounded hover:bg-blue-50">My Profile</a>
        <a href="../auth/logout.php" class="block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Logout</a>
      </div>
    </div>

    <!-- Logout (Desktop) -->
    <div class="hidden md:block">
      <a href="../auth/logout.php" class="bg-red-600 text-white px-5 py-3 rounded-md hover:bg-red-700 font-semibold">Logout</a>
    </div>

  </div>
</header>

<!-- Main Section -->
<main class="p-4">
  <div class="bg-white rounded-xl shadow p-6 min-h-[500px] max-w-full">
    <div class="flex flex-wrap justify-center gap-6">
      <?php
        if (in_array($section, $allowed)) {
            include $section . '.php';
        } else {
            echo "<p class='text-red-500'>Invalid section specified.</p>";
        }
      ?>
    </div>
  </div>
</main>


<!-- Scripts -->
<script>
  lucide.createIcons();

  function toggleMenu(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
  }
</script>
</body>
</html>
