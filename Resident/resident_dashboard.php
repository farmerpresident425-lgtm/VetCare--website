<?php
session_name("resident_session");
session_start();

include '../config/db.php';

// Check if resident is logged in
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: ../../index.php");
    exit;
}

// Fetch resident appointments
$query = $conn->prepare("SELECT a.*, ani.name AS animal_name
                         FROM appointments a
                         JOIN animals ani ON a.animal_id = ani.id
                         WHERE a.resident_id = ?
                         ORDER BY a.appointment_date DESC");
$query->bind_param("i", $userId);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Resident Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
  <h2 class="text-2xl font-bold mb-4 text-blue-600">My Appointments</h2>

  <!-- 📱 Responsive Table Container -->
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto border-collapse">
      <thead>
        <tr class="bg-blue-100 text-left text-sm sm:text-base">
          <th class="border p-2">Animal</th>
          <th class="border p-2">Date</th>
          <th class="border p-2">Time</th>
          <th class="border p-2">Purpose</th>
          <th class="border p-2">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-100 text-sm sm:text-base">
              <td class="border p-2 whitespace-nowrap"><?= htmlspecialchars($row['animal_name']) ?></td>
              <td class="border p-2 whitespace-nowrap"><?= $row['appointment_date'] ?></td>
              <td class="border p-2 whitespace-nowrap"><?= $row['appointment_time'] ?></td>
              <td class="border p-2"><?= htmlspecialchars($row['purpose']) ?></td>
              <td class="border p-2 whitespace-nowrap">
                <?php
                  $statusColor = match($row['status']) {
                      'approved' => 'text-green-600',
                      'pending' => 'text-yellow-600',
                      'cancelled' => 'text-red-500',
                      'completed' => 'text-gray-500',
                      default => 'text-black'
                  };
                ?>
                <span class="<?= $statusColor ?> font-semibold"><?= ucfirst($row['status']) ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center text-gray-500 py-4">No appointments found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


</body>
</html>
