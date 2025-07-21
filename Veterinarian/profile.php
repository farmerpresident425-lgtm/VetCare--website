<?php
if (!isset($conn)) {
    session_start();
    include '../config/db.php';
}

$userId = $_SESSION['user_id'] ?? 0;
$success = false;

// ✅ Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_pic'])) {
    $file = $_FILES['new_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $filename = 'vet_' . uniqid() . '.' . $ext;
            $destination = '../uploads/profile_pics/' . $filename;

            if (!is_dir('../uploads/profile_pics')) {
                mkdir('../uploads/profile_pics', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $filename, $userId);
                $stmt->execute();

                $_SESSION['profile_pic'] = $filename; // update session
                header("Location: ?section=profile&updated=1");
                exit;
            } else {
                $error = "❌ Failed to save file.";
            }
        } else {
            $error = "❌ Invalid file type or exceeds 2MB.";
        }
    } else {
        $error = "❌ Upload error.";
    }
}

// ✅ Get current profile pic from database
$res = $conn->query("SELECT profile_pic FROM users WHERE id = $userId");
$row = $res->fetch_assoc();
$current = $row['profile_pic'] ?? '';
$imgPath = $current ? "../uploads/profile_pics/$current" : "https://i.pravatar.cc/100?u=$userId";
?>

<h2 class="text-2xl font-bold mb-4 text-gray-800">Update Profile Picture</h2>

<div class="flex flex-col items-center gap-4">

  <?php if (isset($_GET['updated'])): ?>
    <p class="text-green-600 font-semibold">✅ Profile picture updated successfully!</p>
  <?php endif; ?>

  <img src="<?= htmlspecialchars($imgPath) ?>?v=<?= time() ?>" alt="Current Picture" class="w-32 h-32 rounded-full object-cover shadow">

  <?php if (!empty($error)) echo "<p class='text-red-600'>$error</p>"; ?>

  <?php if (!isset($_GET['updated'])): ?>
    <form method="POST" enctype="multipart/form-data" class="bg-gray-100 p-6 rounded-lg shadow space-y-4 w-full max-w-sm">
      <label class="block text-sm text-gray-600">Upload New Picture (JPG/PNG, Max 2MB)</label>
      <input type="file" name="new_pic" accept="image/*" required class="w-full border p-2 rounded">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Upload</button>
    </form>
  <?php endif; ?>
</div>

