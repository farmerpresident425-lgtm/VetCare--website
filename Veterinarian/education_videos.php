<?php
ob_start(); // 🔐 Start output buffering
include '../config/db.php';

// ✅ Handle video deletion
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    
    // Get filename first
    $res = $conn->query("SELECT filename FROM education_uploads WHERE id = $deleteId AND filetype='video'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $filePath = 'uploads/videos/' . $row['filename'];

        // Delete file from server
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from DB
        $conn->query("DELETE FROM education_uploads WHERE id = $deleteId");
    }

    // Redirect
    header("Location: main.php?section=education_videos&deleted=1");
    exit;
}

// ✅ Handle video upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    $title = trim($_POST['video_title'] ?? '');
    $file = $_FILES['video_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4', 'mov', 'webm'];

        if (in_array($ext, $allowed) && $file['size'] <= 25 * 1024 * 1024) {
            $filename = uniqid() . '_' . basename($file['name']);
            $folder = 'uploads/videos/';
            $destination = $folder . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("INSERT INTO education_uploads (title, filename, filetype) VALUES (?, ?, 'video')");
                $stmt->bind_param("ss", $title, $filename);
                $stmt->execute();

                header("Location: main.php?section=education_videos&uploaded=1");
                exit;
            } else {
                header("Location: main.php?section=education_videos&error=uploadfail");
                exit;
            }
        } else {
            header("Location: main.php?section=education_videos&error=invalidfile");
            exit;
        }
    } else {
        header("Location: main.php?section=education_videos&error=uploaderror");
        exit;
    }
}

// ✅ Fetch all videos
$videos = $conn->query("SELECT * FROM education_uploads WHERE filetype='video' ORDER BY created_at DESC");
?>

<h2 class="text-2xl font-bold mb-6 text-gray-800">Uploaded Videos</h2>

<!-- ✅ Show Alerts -->
<?php if (isset($_GET['uploaded'])): ?>
  <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">Video uploaded successfully!</div>
<?php elseif (isset($_GET['deleted'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">Video deleted successfully!</div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
    <?php
      switch ($_GET['error']) {
        case 'uploadfail': echo 'Failed to upload file.'; break;
        case 'uploaderror': echo 'Upload error occurred.'; break;
        case 'invalidfile': echo 'Invalid file or too large (max 25MB).'; break;
        default: echo 'Something went wrong.'; break;
      }
    ?>
  </div>
<?php endif; ?>

<!-- ✅ Upload Form -->
<form method="POST" enctype="multipart/form-data" class="mb-8 bg-gray-100 p-6 rounded-lg shadow space-y-4">
  <h3 class="text-lg font-semibold">Upload Video</h3>
  <input type="text" name="video_title" placeholder="Video Title" class="w-full px-4 py-2 border rounded" required>
  <input type="file" name="video_file" accept="video/*" class="w-full border p-2 rounded" required>
  <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Upload Video</button>
</form>

<!-- ✅ Video List -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
  <?php if ($videos->num_rows > 0): ?>
    <?php while ($video = $videos->fetch_assoc()): ?>
      <div class="bg-white p-4 rounded shadow relative">
        <p class="font-semibold mb-2"><?= htmlspecialchars($video['title']) ?></p>
        <video controls class="w-full rounded mb-2">
          <source src="/vet_systems/Veterinarian/uploads/videos/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <!-- ✅ Delete Button -->
        <a href="main.php?section=education_videos&delete_id=<?= $video['id'] ?>" 
           class="text-sm text-red-600 hover:underline" 
           onclick="return confirm('Are you sure you want to delete this video?')">Delete</a>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="text-gray-600 col-span-2">No videos uploaded yet.</div>
  <?php endif; ?>
</div>

<?php ob_end_flush(); ?>
