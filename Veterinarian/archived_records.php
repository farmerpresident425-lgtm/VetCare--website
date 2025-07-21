<?php
include '../config/db.php';

// Pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Total records count
$totalRows = $conn->query("SELECT COUNT(*) AS total FROM animal_health_records WHERE status = 'archived'")->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch archived records
$records = $conn->query("
  SELECT 
    ahr.*, 
    u.name AS owner_name
  FROM animal_health_records ahr
  JOIN users u ON ahr.owner_id = u.id
  WHERE ahr.status = 'archived'
  ORDER BY ahr.date_of_checkup DESC
  LIMIT $limit OFFSET $offset
");
?>

<h2 class="text-xl font-bold mb-4">Archived Animal Health Records</h2>

<table class="min-w-full table-auto border border-gray-300 text-sm">
  <thead class="bg-gray-100">
    <tr>
      
      <th class="border px-2 py-2">Owner</th>
      <th class="border px-2 py-2">Breed</th>
      <th class="border px-2 py-2">Sex</th>
      <th class="border px-2 py-2">Birth Date</th>
      <th class="border px-2 py-2">Color</th>
      <th class="border px-2 py-2">Weight (kg)</th>
      <th class="border px-2 py-2">Mark</th>
      <th class="border px-2 py-2">Checkup</th>
      <th class="border px-2 py-2">Diagnosis</th>
      <th class="border px-2 py-2">Treatment</th>
      <th class="border px-2 py-2">Medication</th>
      <th class="border px-2 py-2">Dosage</th>
      <th class="border px-2 py-2">Next Checkup</th>
      <th class="border px-2 py-2">Vaccine</th>
      <th class="border px-2 py-2">Vaccine Type</th>
      <th class="border px-2 py-2">Remarks</th>
      <th class="border px-2 py-2">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($records->num_rows > 0): ?>
      <?php while ($rec = $records->fetch_assoc()): ?>
        <tr>
          
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['owner_name']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['breed']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['sex']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['birth_date']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['color']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['weight_kg']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['identification_mark']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['date_of_checkup']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['diagnosis']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['treatment_given']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['medication_prescribed']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['dosage']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['next_checkup_date']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['vaccine_administered']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['vaccine_type']) ?></td>
          <td class="border px-2 py-1"><?= htmlspecialchars($rec['remarks']) ?></td>
          <td class="border px-2 py-1 text-center whitespace-nowrap">
            <div class="flex gap-1 justify-center">
              <!-- Restore Button -->
              <form action="restore_record.php" method="POST">
                <input type="hidden" name="id" value="<?= $rec['id'] ?>">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">Restore</button>
              </form>
              <!-- Delete Button -->
              <form action="delete_permanent.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this record?');">
                <input type="hidden" name="id" value="<?= $rec['id'] ?>">
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="19" class="text-center text-gray-500 py-4">No archived records found.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
  <div class="mt-4 flex justify-center space-x-2">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?section=archived_records&page=<?= $i ?>" class="px-3 py-1 rounded border <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-white text-gray-700' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
<?php endif; ?>
