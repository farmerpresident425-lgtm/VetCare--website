<?php 
ob_start();
include '../config/db.php';

// ✅ Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("SELECT filename FROM education_uploads WHERE id = $id AND filetype = 'image'");
    if ($row = $result->fetch_assoc()) {
        $filePath = 'uploads/images/' . $row['filename'];
        if (file_exists($filePath)) unlink($filePath);
        $conn->query("DELETE FROM education_uploads WHERE id = $id");
    }
    header("Location: main.php?section=education_images&deleted=1");
    exit;
}

// ✅ Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file'])) {
    $title = trim($_POST['image_title'] ?? '');
    $file = $_FILES['image_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $filename = uniqid() . '_' . basename($file['name']);
            $folder = 'uploads/images/';
            $destination = $folder . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("INSERT INTO education_uploads (title, filename, filetype) VALUES (?, ?, 'image')");
                $stmt->bind_param("ss", $title, $filename);
                $stmt->execute();
                header("Location: main.php?section=education_images&uploaded=1");
                exit;
            } else {
                header("Location: main.php?section=education_images&error=uploadfail");
                exit;
            }
        } else {
            header("Location: main.php?section=education_images&error=invalidfile");
            exit;
        }
    } else {
        header("Location: main.php?section=education_images&error=uploaderror");
        exit;
    }
}

// ✅ Fetch all images
$images = $conn->query("SELECT * FROM education_uploads WHERE filetype='image' ORDER BY created_at DESC");
?>

<h2 class="text-2xl font-bold mb-6 text-gray-800">Uploaded Images</h2>

<!-- ✅ Alerts -->
<?php if (isset($_GET['uploaded'])): ?>
  <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">Image uploaded successfully!</div>
<?php elseif (isset($_GET['deleted'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">Image deleted.</div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
    <?php
      switch ($_GET['error']) {
        case 'uploadfail': echo 'Failed to upload file.'; break;
        case 'uploaderror': echo 'Upload error occurred.'; break;
        case 'invalidfile': echo 'Invalid file or too large (max 5MB).'; break;
        default: echo 'Something went wrong.'; break;
      }
    ?>
  </div>
<?php endif; ?>

<!-- ✅ Upload Form -->
<form method="POST" enctype="multipart/form-data" class="mb-8 bg-gray-100 p-6 rounded-lg shadow space-y-4">
  <h3 class="text-lg font-semibold">Upload Image</h3>
  <input type="text" name="image_title" placeholder="Image Title" class="w-full px-4 py-2 border rounded" required>
  <input type="file" name="image_file" accept="image/*" class="w-full border p-2 rounded" required>
  <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Upload Image</button>
</form>

<!-- ✅ Image Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 mb-10">
  <?php if ($images->num_rows > 0): ?>
    <?php while ($img = $images->fetch_assoc()): ?>
      <div class="bg-white p-4 rounded shadow text-center relative">
        <img src="/vet_systems/Veterinarian/uploads/images/<?= htmlspecialchars($img['filename']) ?>"
             alt="<?= htmlspecialchars($img['title']) ?>"
             class="w-full h-40 object-cover rounded mb-2"
             onerror="this.src='/vet_systems/Veterinarian/placeholder.png';">
        <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($img['title']) ?></p>
        <a href="/vet_systems/Veterinarian/uploads/images/<?= htmlspecialchars($img['filename']) ?>"
           download class="text-xs text-blue-600 hover:underline block">⬇️ Download</a>

        <!-- ✅ Delete Button -->
        <div class="flex justify-center gap-3 mt-2">
          <a href="main.php?section=education_images&delete=<?= $img['id'] ?>"
             onclick="return confirm('Are you sure you want to delete this image?')"
             class="text-red-600 text-sm hover:underline">Delete</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="text-gray-600 col-span-2">No images uploaded yet.</div>
  <?php endif; ?>
</div>

<?php ob_end_flush(); ?>
