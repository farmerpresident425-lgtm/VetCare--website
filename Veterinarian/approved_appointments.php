<?php
include '../config/db.php';

$query = $conn->query("
  SELECT a.id, a.appointment_date, a.appointment_time, a.purpose, u.name AS resident_name 
  FROM appointments a
  JOIN users u ON a.resident_id = u.id
  WHERE a.status = 'approved'
  ORDER BY a.appointment_date ASC
");
?>

<h2 class="text-2xl font-bold mb-4">Approved Appointments</h2>

<!-- ✅ Desktop Table View -->
<div class="hidden sm:block">
  <table class="min-w-full bg-white rounded-lg overflow-hidden shadow text-sm">
    <thead class="bg-gray-200">
      <tr>
        <th class="text-left px-4 py-2">#</th>
        <th class="text-left px-4 py-2">Resident</th>
        <th class="text-left px-4 py-2">Date</th>
        <th class="text-left px-4 py-2">Time</th>
        <th class="text-left px-4 py-2">Reason</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($query->num_rows > 0): ?>
        <?php $no = 1; while ($row = $query->fetch_assoc()): ?>
          <tr class="border-t hover:bg-gray-50">
            <td class="px-4 py-2"><?= $no++ ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['resident_name']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['appointment_date']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['appointment_time']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['purpose']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" class="text-center px-4 py-4 text-gray-500">No approved appointments yet.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ✅ Mobile View -->
<div class="sm:hidden space-y-4">
  <?php
  // Reset result pointer
  if ($query->num_rows > 0) {
    $query->data_seek(0);
    $no = 1;
    while ($row = $query->fetch_assoc()):
  ?>
    <div class="bg-white shadow rounded-lg p-4 text-sm border">
      <p><span class="font-bold">#:</span> <?= $no++ ?></p>
      <p><span class="font-bold">Resident:</span> <?= htmlspecialchars($row['resident_name']) ?></p>
      <p><span class="font-bold">Date:</span> <?= htmlspecialchars($row['appointment_date']) ?></p>
      <p><span class="font-bold">Time:</span> <?= htmlspecialchars($row['appointment_time']) ?></p>
      <p><span class="font-bold">Reason:</span> <?= htmlspecialchars($row['purpose']) ?></p>
    </div>
  <?php endwhile; } else { ?>
    <div class="text-center text-gray-500">No approved appointments yet.</div>
  <?php } ?>
</div>
