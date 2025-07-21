<?php
if (!isset($conn)) {
    session_start();
    include '../config/db.php';
}

$userId = $_SESSION['user_id'] ?? 0;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_pic'])) {
    $file = $_FILES['new_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $filename = 'admin_' . uniqid() . '.' . $ext;
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
            $error = "❌ Invalid file type or file exceeds 2MB.";
        }
    } else {
        $error = "❌ Upload error.";
    }
}

// Fetch current image
$res = $conn->query("SELECT profile_pic FROM users WHERE id = $userId");
$row = $res->fetch_assoc();
$current = $row['profile_pic'] ?? '';
$imgPath = $current ? "../uploads/profile_pics/$current" : "https://i.pravatar.cc/100?u=$userId";
?>

<!-- 📸 Profile Upload Interface -->
<div class="w-full max-w-md mx-auto bg-white p-6 rounded-xl shadow mt-8 space-y-6">
    <h2 class="text-2xl sm:text-3xl font-bold text-center text-gray-800">Update Profile Picture</h2>

    <!-- ✅ Success Message -->
    <?php if (isset($_GET['updated'])): ?>
        <p class="text-green-600 font-semibold text-center">✅ Profile picture updated successfully!</p>
    <?php endif; ?>

    <!-- 👤 Current Picture -->
    <div class="flex justify-center">
        <img src="<?= htmlspecialchars($imgPath) ?>?v=<?= time() ?>"
             alt="Profile Picture"
             class="w-32 h-32 sm:w-36 sm:h-36 rounded-full border-4 border-teal-300 object-cover shadow">
    </div>

    <!-- ❌ Error -->
    <?php if (!isset($_GET['updated']) && !empty($error)): ?>
        <p class="text-red-600 text-center bg-red-100 px-4 py-2 rounded"><?= $error ?></p>
    <?php endif; ?>

    <!-- 📤 Upload Form -->
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">Choose New Picture (JPG/PNG, Max 2MB)</label>
        <input type="file" name="new_pic" accept="image/*" required
               class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition font-semibold">
            Upload
        </button>
    </form>
</div>
