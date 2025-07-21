<?php
ob_start(); // ✅ Start output buffering to prevent header errors
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: ../../index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ✅ Handle auto profile upload
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

$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

$fullName = !empty($user['name']) ? $user['name'] : 'Veterinarian';
$profilePic = !empty($user['profile_pic']) 
    ? '../uploads/profile_pics/' . $user['profile_pic'] 
    : 'https://i.pravatar.cc/100?u=' . $userId;

$section = $_GET['section'] ?? 'overview';

$allowed = [
  'overview', 'records', 'messages', 'group_chat', 'reports',
  'add_health_record', 'view_health_records', 'edit_health_record',
  'archived_records', 'add_appointment', 'edit_appointment',
  'appointment_request', 'animals', 'approved_appointments',
  'education_links', 'profile', 'education_videos', 'education_images', 'view_animals'
];

function isActive($sec, $current) {
  return $sec === $current ? 'font-semibold bg-green-200' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Veterinarian Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<!-- ✅ Top Navbar (Mobile Only) -->
<div class="bg-white shadow-md sm:hidden flex justify-between items-center px-4 py-3">
  <form id="mobileProfileForm" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
    <label for="mobileUpload" class="cursor-pointer">
      <img src="<?= htmlspecialchars($profilePic) ?>?v=<?= time() ?>" alt="Profile"
        class="w-10 h-10 rounded-full object-cover border-2 border-green-500" />
    </label>
    <input type="file" name="new_profile" id="mobileUpload" accept="image/*" class="hidden"
      onchange="document.getElementById('mobileProfileForm').submit();">
    <input type="hidden" name="upload_profile" value="1">
    <p class="font-medium text-gray-800"><?= htmlspecialchars($fullName) ?></p>
  </form>
  <button onclick="toggleSidebar()" class="text-green-700 ml-2">
    <i data-lucide="menu" class="w-6 h-6"></i>
  </button>
</div>

<!-- ✅ Layout Container -->
<div class="flex flex-col sm:flex-row">

<!-- ✅ Sidebar -->
<aside id="sidebar"
class="w-full sm:w-64 sm:h-screen hidden sm:flex flex-col gap-4 sm:sticky top-0 bg-green-100 text-green-800 shadow-lg p-4 z-50 sm:block">

  <!-- 👤 Sidebar Profile (Desktop Only) -->
  <form id="desktopProfileForm" method="POST" enctype="multipart/form-data" class="hidden sm:flex flex-col items-center mb-4">
    <label for="desktopUpload" class="cursor-pointer">
      <img src="<?= htmlspecialchars($profilePic) ?>?v=<?= time() ?>" alt="Profile"
        class="w-20 h-20 rounded-full object-cover border-4 border-green-500 hover:opacity-90 transition" />
    </label>
    <input type="file" name="new_profile" id="desktopUpload" accept="image/*" class="hidden"
      onchange="document.getElementById('desktopProfileForm').submit();">
    <input type="hidden" name="upload_profile" value="1">
    <p class="font-semibold text-lg text-center mt-2"><?= htmlspecialchars($fullName) ?></p>
  </form>

  <!-- 🔗 Navigation -->
  <a href="?section=overview" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('overview', $section) ?>">Home</a>
  <a href="?section=records" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('records', $section) ?>">Animal Records</a>
  <a href="?section=appointment_request" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('appointment_request', $section) ?>">Appointments</a>
  <a href="?section=approved_appointments" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('approved_appointments', $section) ?>">Approved Appointments</a>
  <a href="?section=messages" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('messages', $section) ?>">Messages</a>

  <!-- 📚 Learning Materials Dropdown -->
  <div class="relative">
    <button onclick="toggleDropdown()" class="w-full text-left px-4 py-2 rounded hover:bg-green-200 font-medium <?= isActive('education_links', $section) ?>">
      Learning Materials
    </button>
    <div id="learningDropdown" class="mt-1 w-full bg-white border border-gray-200 rounded shadow-md z-50 hidden">
      <a href="?section=education_links" class="block px-4 py-2 hover:bg-green-100 text-gray-800"> Links</a>
      <a href="?section=education_videos" class="block px-4 py-2 hover:bg-green-100 text-gray-800"> Videos</a>
      <a href="?section=education_images" class="block px-4 py-2 hover:bg-green-100 text-gray-800"> Images</a>
    </div>
  </div>

  <a href="?section=reports" class="px-4 py-2 rounded hover:bg-green-200 <?= isActive('reports', $section) ?>">Reports</a>

  <!-- 🔴 Logout -->
  <a href="../auth/logout.php" class="mt-4 bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 flex items-center gap-2">
    <i data-lucide="log-out" class="w-5 h-5"></i> Logout
  </a>
</aside>

<!-- ✅ Mobile Menu -->
<div id="mobileMenu" class="sm:hidden hidden px-6 pb-6">
  <div class="bg-white rounded-xl shadow-md mt-3 p-4 space-y-2">
    <a href="?section=overview" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Home</a>
    <a href="?section=records" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Animal Records</a>
    <a href="?section=appointment_request" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Appointments</a>
    <a href="?section=approved_appointments" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Approved Appointments</a>
    <a href="?section=messages" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Messages</a>

    <div class="relative group hidden md:block">
      <a href="#" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Learning Materials</a>
      <div class="absolute left-0 mt-1 w-48 hidden group-hover:block bg-white border border-gray-200 rounded shadow-md z-50">
        <a href="?section=education_links" class="block px-4 py-2 hover:bg-green-100 text-gray-800">📎 Links</a>
        <a href="?section=education_videos" class="block px-4 py-2 hover:bg-green-100 text-gray-800">🎥 Videos</a>
        <a href="?section=education_images" class="block px-4 py-2 hover:bg-green-100 text-gray-800">🖼️ Images</a>
      </div>
    </div>

    <a href="?section=reports" class="block w-full bg-green-100 text-green-900 text-center px-4 py-2 rounded hover:bg-green-200 font-medium">Reports</a>
    <a href="../auth/logout.php" class="block w-full bg-red-500 text-white text-center px-4 py-2 rounded hover:bg-red-600 font-medium">Logout</a>
  </div>
</div>

<!-- ✅ Main Content -->
<main class="w-full p-4 sm:p-6">
  <div class="bg-white rounded-xl shadow p-4 sm:p-6 min-h-[400px]">
    <?php
      if (in_array($section, $allowed)) {
          $path = $section . '.php';
          if (file_exists($path)) {
              include $path;
          } else {
              echo "<p class='text-red-500'>⚠️ Section file not found: $section.php</p>";
          }
      } else {
          echo "<p class='text-red-500'>🚫 Invalid section specified.</p>";
      }
    ?>
  </div>
</main>

</div>

<script>
  function toggleSidebar() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
  }

  document.querySelectorAll('#mobileMenu a').forEach(link => {
    link.addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.add('hidden');
    });
  });

  lucide.createIcons();
</script>

<script>
  function toggleDropdown() {
    const menu = document.getElementById("learningDropdown");
    if (menu) {
      menu.classList.toggle("hidden");
    }
  }

  document.addEventListener('click', function (e) {
    const dropdown = document.getElementById('learningDropdown');
    const toggleBtn = e.target.closest('button[onclick="toggleDropdown()"]');

    if (!toggleBtn && dropdown && !dropdown.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });
</script>

<?php ob_end_flush(); // ✅ End output buffering ?>
</body>
</html>
