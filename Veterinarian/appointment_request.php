<?php

include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: ../../index.php");
    exit;
}

$query = $conn->query("SELECT a.*, an.animal_name AS animal_name, u.name AS resident_name 
                       FROM appointments a 
                       JOIN animals an ON a.animal_id = an.id 
                       JOIN users u ON a.resident_id = u.id 
                       WHERE a.status = 'pending'
                       ORDER BY a.appointment_date ASC");
?>

<h2 class="text-xl font-bold mb-4">Appointment Requests</h2>

<!-- ✅ Desktop Table -->
<div class="hidden sm:block">
  <table class="w-full border text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="p-2 border">Animal Name</th>
        <th class="p-2 border">Resident</th>
        <th class="p-2 border">Date</th>
        <th class="p-2 border">Time</th>
        <th class="p-2 border">Purpose</th>
        <th class="p-2 border">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $query->fetch_assoc()): ?>
        <tr>
          <td class="p-2 border"><?= htmlspecialchars($row['animal_name']) ?></td>
          <td class="p-2 border"><?= htmlspecialchars($row['resident_name']) ?></td>
          <td class="p-2 border"><?= $row['appointment_date'] ?></td>
          <td class="p-2 border"><?= $row['appointment_time'] ?></td>
          <td class="p-2 border"><?= htmlspecialchars($row['purpose']) ?></td>
          <td class="p-2 border text-center">
            <a href="submit_appointment.php?id=<?= $row['id'] ?>&action=approve" class="text-green-600">Approve</a> |
            <button onclick="openModal(<?= $row['id'] ?>)" class="text-red-600">Cancel</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- ✅ Mobile Card View -->
<div class="space-y-4 sm:hidden">
  <?php $query->data_seek(0); while ($row = $query->fetch_assoc()): ?>
    <div class="bg-white p-4 rounded shadow border">
      <p><strong>Animal:</strong> <?= htmlspecialchars($row['animal_name']) ?></p>
      <p><strong>Resident:</strong> <?= htmlspecialchars($row['resident_name']) ?></p>
      <p><strong>Date:</strong> <?= $row['appointment_date'] ?></p>
      <p><strong>Time:</strong> <?= $row['appointment_time'] ?></p>
      <p><strong>Purpose:</strong> <?= htmlspecialchars($row['purpose']) ?></p>
      <div class="flex gap-4 mt-2">
        <a href="submit_appointment.php?id=<?= $row['id'] ?>&action=approve" class="text-green-600 font-semibold">Approve</a>
        <button onclick="openModal(<?= $row['id'] ?>)" class="text-red-600 font-semibold">Cancel</button>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<!-- 🔴 Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 hidden bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white p-6 rounded-lg w-96 shadow">
    <h2 class="text-lg font-semibold mb-4">Reason for Cancellation</h2>
    <form method="POST" action="submit_appointment.php">
      <input type="hidden" name="id" id="cancelAppointmentId">
      <input type="hidden" name="action" value="cancel">
      <textarea name="reason" required placeholder="Enter reason..." class="w-full border rounded p-2 mb-4"></textarea>
      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Close</button>
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Submit</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(appointmentId) {
    document.getElementById('cancelAppointmentId').value = appointmentId;
    document.getElementById('cancelModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('cancelModal').classList.add('hidden');
  }
</script>
