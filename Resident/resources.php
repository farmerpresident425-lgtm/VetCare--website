<?php
include '../config/db.php';

$tab = $_GET['tab'] ?? 'links';

$links = $conn->query("SELECT * FROM education_materials ORDER BY created_at DESC");
$videos = $conn->query("SELECT * FROM education_uploads WHERE filetype='video' ORDER BY created_at DESC");
$images = $conn->query("SELECT * FROM education_uploads WHERE filetype='image' ORDER BY created_at DESC");
?>

<!-- 🔘 Tabs Navigation (Visible only on small screens) -->
<div class="w-full mb-6 sm:hidden">
  <div class="flex justify-center flex-wrap gap-3 text-sm font-medium">
    <a href="?section=resources&tab=links"
       class="px-4 py-1 rounded-full transition <?= $tab === 'links' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-green-100' ?>">
       Links
    </a>
    <a href="?section=resources&tab=videos"
       class="px-4 py-1 rounded-full transition <?= $tab === 'videos' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-green-100' ?>">
      Videos
    </a>
    <a href="?section=resources&tab=images"
       class="px-4 py-1 rounded-full transition <?= $tab === 'images' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-green-100' ?>">
      Images
    </a>
  </div>
</div>

<!-- 🔗 LINKS -->
<?php if ($tab === 'links'): ?>
<div id="linksSection" class="mb-10 w-full px-2 sm:px-4 lg:px-6">
  <h3 class="text-xl font-semibold mb-4 text-green-700">Shared Links</h3>

  <ul class="space-y-4 w-full">
    <?php while ($row = $links->fetch_assoc()): ?>
      <li class="w-full p-4 bg-white shadow-md rounded-lg border border-gray-200 text-sm sm:text-base">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center">
          <div class="flex-1">
            <p class="font-bold text-gray-800 text-lg sm:text-xl"><?= htmlspecialchars($row['title']) ?></p>
            <a href="<?= htmlspecialchars($row['link']) ?>" 
               target="_blank"
               class="text-blue-700 font-semibold hover:underline break-words block mt-1">
              🌐 View Link
            </a>
            <p class="text-xs text-gray-500 mt-2">Shared on <?= date('F j, Y', strtotime($row['created_at'])) ?></p>
          </div>
        </div>
      </li>
    <?php endwhile; ?>
  </ul>
</div>
<?php endif; ?>

<!-- 🎥 VIDEOS -->
<?php if ($tab === 'videos'): ?>
  <div id="videosSection" class="mb-10 w-full px-2 sm:px-4 lg:px-6">
    <h3 class="text-xl font-semibold mb-4 text-green-700">Videos</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <?php while ($video = $videos->fetch_assoc()): ?>
        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
          <p class="font-semibold mb-2 text-gray-800"><?= htmlspecialchars($video['title']) ?></p>
          <div class="w-full aspect-w-16 aspect-h-9 mb-2">
            <video controls class="w-full h-full object-cover rounded-lg bg-black">
              <source src="/vet_systems/Veterinarian/uploads/videos/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
              Your browser does not support the video tag.
            </video>
          </div>
          <a href="/vet_systems/Veterinarian/uploads/videos/<?= htmlspecialchars($video['filename']) ?>" download class="block text-sm text-blue-600 hover:underline">⬇️ Download Video</a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endif; ?>

<!-- 🖼 IMAGES -->
<?php if ($tab === 'images'): ?>
  <div id="imagesSection" class="mb-10 w-full px-2 sm:px-4 lg:px-6">
    <h3 class="text-xl font-semibold mb-4 text-green-700">Images</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <?php while ($img = $images->fetch_assoc()): ?>
        <div class="bg-white p-3 rounded-lg shadow border border-gray-200 text-center">
          <img src="/vet_systems/Veterinarian/uploads/images/<?= htmlspecialchars($img['filename']) ?>"
               onerror="this.src='/vet_systems/Veterinarian/placeholder.png';"
               class="w-full h-40 sm:h-48 object-cover rounded mb-2">
          <p class="text-sm text-gray-700 truncate"><?= htmlspecialchars($img['title']) ?></p>
          <a href="/vet_systems/Veterinarian/uploads/images/<?= htmlspecialchars($img['filename']) ?>" download class="text-blue-500 text-xs hover:underline">⬇️ Download Image</a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Tailwind Aspect Ratio Fallback -->
<style>
  .aspect-w-16 { aspect-ratio: 16 / 9; }
  .aspect-h-9 { aspect-ratio: 16 / 9; }
</style>
