<?php
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

$residentId = $_SESSION['user_id'];

$query = "
    SELECT a.id, a.appointment_date, a.appointment_time, a.purpose, a.status, a.cancel_reason AS remarks, animals.animal_name AS animal_name
    FROM appointments a
    JOIN animals ON a.animal_id = animals.id
    WHERE a.resident_id = ?
    ORDER BY a.appointment_date DESC
";


// Prepare query
$appointments = $conn->prepare($query);
$appointments->bind_param("i", $residentId);

// Execute for mobile
$appointments->execute();
$mobile_results = $appointments->get_result();

// Execute again for desktop
$appointments->execute();
$desktop_results = $appointments->get_result();
?>

<!-- ✅ Main Wrapper Container -->
<div class="w-full px-4 md:px-6 lg:px-8">

<!-- 📱 Mobile View (Card Style) -->
<div class="md:hidden space-y-4 px-4">
  <?php while ($row = $mobile_results->fetch_assoc()): ?>
    <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
      <div class="text-sm text-gray-700 mb-1"><span class="font-semibold">Animal:</span> <?= htmlspecialchars($row['animal_name']) ?></div>
      <div class="text-sm text-gray-700 mb-1"><span class="font-semibold">Date:</span> <?= htmlspecialchars($row['appointment_date']) ?></div>
      <div class="text-sm text-gray-700 mb-1"><span class="font-semibold">Time:</span> <?= date("g:i A", strtotime($row['appointment_time'])) ?></div>
      <div class="text-sm text-gray-700 mb-1"><span class="font-semibold">Purpose:</span> <?= htmlspecialchars($row['purpose']) ?></div>
      <div class="text-sm text-gray-700 mb-2"><span class="font-semibold">Status:</span> 
        <span class="px-2 py-1 rounded text-xs font-medium <?= match($row['status']) {
            'approved' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700'
        } ?>">
          <?= ucfirst($row['status']) ?>
        </span>
        <?php if ($row['status'] === 'cancelled' && !empty($row['remarks'])): ?>
  <div class="text-sm text-red-600"><span class="font-semibold">Reason:</span> <?= htmlspecialchars($row['remarks']) ?></div>
<?php endif; ?>

      </div>

      <?php if ($row['status'] === 'pending'): ?>
        <div class="flex justify-start gap-2">
          <a href="edit_appointment.php?id=<?= $row['id'] ?>" 
             class="bg-blue-100 text-blue-800 border border-blue-300 px-3 py-1 rounded text-xs hover:bg-blue-200">
            Edit
          </a>
          <a href="delete_appointment.php?id=<?= $row['id'] ?>" 
             onclick="return confirm('Are you sure?')"
             class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
            Delete
          </a>
        </div>
      <?php else: ?>
        <div class="text-xs text-gray-400 italic">Locked</div>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>

<!-- 💻 Desktop View (Wider Table Layout) -->
<div class="hidden md:block mt-6 px-4">
  <div class="w-full overflow-x-auto">
    <table class="w-full text-sm border border-gray-300 rounded-lg shadow-lg bg-white">
      <thead class="bg-yellow-100 text-yellow-900 text-center">
  <tr>
    <th class="px-4 py-3 border">Animal Name</th>
    <th class="px-4 py-3 border">Date</th>
    <th class="px-4 py-3 border">Time</th>
    <th class="px-4 py-3 border">Purpose</th>
    <th class="px-4 py-3 border">Status</th>
    <th class="px-4 py-3 border">Reason</th>

    <th class="px-4 py-3 border">Actions</th>

  </tr>
</thead>

      <tbody>
        <?php while ($row = $desktop_results->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 border-t">
          <td class="px-4 py-2 text-center"><?= htmlspecialchars($row['animal_name']) ?></td>
<td class="px-4 py-2 text-center"><?= htmlspecialchars($row['appointment_date']) ?></td>
<td class="px-4 py-2 text-center"><?= date("g:i A", strtotime($row['appointment_time'])) ?></td>
<td class="px-4 py-2 text-center"><?= htmlspecialchars($row['purpose']) ?></td>
<td class="px-4 py-2 text-center">
            <span class="px-2 py-1 rounded text-xs font-medium <?= match($row['status']) {
                'approved' => 'bg-green-100 text-green-800',
                'pending' => 'bg-yellow-100 text-yellow-800',
                'completed' => 'bg-blue-100 text-blue-800',
                'cancelled' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-700'
            } ?>">
              <?= ucfirst($row['status']) ?>
            </span>
            <td class="px-4 py-2 text-center text-sm text-red-600">
  <?= $row['status'] === 'cancelled' && !empty($row['remarks']) ? htmlspecialchars($row['remarks']) : '-' ?>
</td>

          </td>
          <td class="px-4 py-2 text-center">
            <?php if ($row['status'] === 'pending'): ?>
              <div class="flex justify-center gap-2">
                <a href="edit_appointment.php?id=<?= $row['id'] ?>" 
                   class="bg-blue-50 text-blue-800 border border-blue-200 px-3 py-1 rounded text-xs hover:bg-blue-100">
                  Edit
                </a>
                <a href="delete_appointment.php?id=<?= $row['id'] ?>" 
                   onclick="return confirm('Are you sure?')"
                   class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                  Delete
                </a>
              </div>
            <?php else: ?>
              <span class="text-gray-400 italic text-xs">Locked</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
