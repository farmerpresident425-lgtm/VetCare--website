<?php
if (!isset($conn)) {
    session_start();
    include '../config/db.php';
}

$userId = $_SESSION['user_id'] ?? 0;
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_pic'])) {
    $file = $_FILES['new_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $filename = 'resident_' . uniqid() . '.' . $ext;
            $destination = '../uploads/profile_pics/' . $filename;

            if (!is_dir('../uploads/profile_pics')) {
                mkdir('../uploads/profile_pics', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $filename, $userId);
                $stmt->execute();

                $_SESSION['profile_pic'] = $filename;
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

$res = $conn->query("SELECT profile_pic FROM users WHERE id = $userId");
$row = $res->fetch_assoc();
$current = $row['profile_pic'] ?? '';
$imgPath = $current ? "../uploads/profile_pics/$current" : "../../assets/default-profile.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile Picture</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">

  <div class="max-w-md w-full mx-auto px-4 py-6 bg-white rounded-xl shadow-md">

    <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Update Profile Picture</h2>

    <div class="flex flex-col items-center gap-4">

      <?php if (isset($_GET['updated'])): ?>
        <p class="text-green-600 font-semibold text-center">✅ Profile picture updated successfully!</p>
      <?php elseif (!empty($error)): ?>
        <p class="text-red-600 text-center"><?= $error ?></p>
      <?php endif; ?>

      <!-- ✅ Profile Image -->
      <img 
        src="<?= htmlspecialchars($imgPath) . '?v=' . time() ?>" 
        alt="Current Picture" 
        class="w-28 h-28 rounded-full border-4 border-blue-300 shadow object-cover"
      >

      <!-- ✅ Upload Form -->
      <form method="POST" enctype="multipart/form-data" class="w-full space-y-4">
        <div>
          <label class="block text-sm text-gray-700 font-medium mb-1">Upload New Picture (JPG/PNG, Max 2MB)</label>
          <input type="file" name="new_pic" accept="image/*" required class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold transition">
          Upload
        </button>
      </form>
    </div>
  </div>

</body>
</html>
