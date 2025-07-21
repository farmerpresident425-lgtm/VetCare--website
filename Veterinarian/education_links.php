<?php
include '../config/db.php';

// ✅ Insert new link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_title'], $_POST['link_url'])) {
    $title = trim($_POST['link_title']);
    $link = trim($_POST['link_url']);

    if (!empty($title) && filter_var($link, FILTER_VALIDATE_URL)) {
        $stmt = $conn->prepare("INSERT INTO education_materials (title, link) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $link);
        $stmt->execute();
        header("Location: main.php?section=education_links&success=1");
        exit;
    } else {
        header("Location: main.php?section=education_links&error=1");
        exit;
    }
}

// ✅ Handle delete
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $conn->query("DELETE FROM education_materials WHERE id = $deleteId");
    header("Location: main.php?section=education_links&deleted=1");
    exit;
}

// ✅ Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'], $_POST['edit_title'], $_POST['edit_link'])) {
    $id = intval($_POST['edit_id']);
    $title = trim($_POST['edit_title']);
    $link = trim($_POST['edit_link']);

    if (!empty($title) && filter_var($link, FILTER_VALIDATE_URL)) {
        $stmt = $conn->prepare("UPDATE education_materials SET title = ?, link = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $link, $id);
        $stmt->execute();
        header("Location: main.php?section=education_links&updated=1");
        exit;
    } else {
        header("Location: main.php?section=education_links&error=2");
        exit;
    }
}

// ✅ Fetch all links
$links = $conn->query("SELECT * FROM education_materials ORDER BY created_at DESC");
?>

<h2 class="text-2xl font-bold mb-4 text-gray-800">Educational Links</h2>

<?php if (isset($_GET['success'])): ?>
  <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">Link uploaded successfully!</div>
<?php elseif (isset($_GET['deleted'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Link deleted successfully.</div>
<?php elseif (isset($_GET['updated'])): ?>
  <div class="bg-blue-100 text-blue-700 px-4 py-2 rounded mb-4">Link updated successfully.</div>
<?php elseif (isset($_GET['error'])): ?>
  <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
    <?= ($_GET['error'] == 1)
      ? 'Please provide a valid title and URL.'
      : 'Failed to update. Make sure the link is valid.'; ?>
  </div>
<?php endif; ?>

<!-- Upload Link Form -->
<form method="POST" class="bg-gray-100 p-4 rounded mb-6 shadow space-y-4">
  <input type="text" name="link_title" placeholder="Link Title" class="w-full px-4 py-2 border rounded" required>
  <input type="url" name="link_url" placeholder="https://example.com/resource" class="w-full px-4 py-2 border rounded" required>
  <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Upload Link</button>
</form>

<!-- Link List -->
<ul class="space-y-4">
  <?php while ($row = $links->fetch_assoc()): ?>
    <li class="bg-white p-4 rounded shadow relative">
      <p class="font-semibold"><?= htmlspecialchars($row['title']) ?></p>
      <a href="<?= htmlspecialchars($row['link']) ?>" class="text-blue-600 hover:underline" target="_blank">Visit</a>

      <div class="mt-2 flex gap-2">
        <a href="main.php?section=education_links&delete_id=<?= $row['id'] ?>" 
           class="text-sm text-red-600 hover:underline"
           onclick="return confirm('Are you sure you want to delete this link?')">Delete</a>
      </div>
    </li>
  <?php endwhile; ?>
</ul>
