<?php
include '../config/db.php';

// Fetch residents with approved status
$residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalRows = $conn->query("SELECT COUNT(*) AS total FROM animal_health_records WHERE status = 'Active'")->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<!-- ✅ Main Content Wrapper -->
<div id="records">

  <!-- 🔍 Search Bar -->
  <div class="flex justify-end mb-4">
    <input type="text" id="recordSearch" placeholder="Search resident name..."
           class="px-4 py-2 border border-gray-300 rounded w-[250px]">
  </div>

  <!-- 👤 Residents Table -->
  <div class="overflow-x-auto mb-8">
    <table class="min-w-full table-auto border border-gray-300 text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">Resident Name</th>
          <th class="border px-4 py-2">Animals Registered</th>
          <th class="border px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $residents->fetch_assoc()): ?>
          <?php
            // Count how many animals this resident has registered
            $resident_id = $row['id'];
            $animal_count_result = $conn->query("SELECT COUNT(*) AS animal_count FROM animals WHERE owner_id = $resident_id");
            $animal_count = $animal_count_result->fetch_assoc()['animal_count'];
          ?>
          <tr>
            <td class="border px-4 py-2 font-medium"><?= htmlspecialchars($row['name']) ?></td>
            <td class="border px-4 py-2 text-center">
              <?= $animal_count > 0 ? "<a href='main.php?section=view_animals&resident_id=$resident_id' class='text-blue-600 hover:underline'>$animal_count animal(s)</a>" : "0" ?>
            </td>
            <td class="border px-4 py-2">
              <div class="flex gap-2 justify-center">
                <a href="main.php?section=add_health_record&resident_id=<?= $resident_id ?>"
                   class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Add Health Record</a>
                <a href="main.php?section=view_health_records&resident_id=<?= $resident_id ?>&show=1"
                   class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">View Health Records</a>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- 📄 Pagination -->
  <div class="mt-6 flex justify-center gap-2">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?section=records&page=<?= $i ?>"
         class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 hover:bg-blue-100' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

<!-- 🔎 JS for Search -->
<script>
document.getElementById('recordSearch').addEventListener('input', function () {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('table tbody tr');

  rows.forEach(row => {
    const nameCell = row.querySelector('td:first-child');
    const text = nameCell.textContent.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});
</script>
