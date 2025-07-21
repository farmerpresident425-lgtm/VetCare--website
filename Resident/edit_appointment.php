<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$residentId = $_SESSION['user_id'];

if (!is_numeric($id) || $id <= 0) {
    echo "<p class='text-red-600 text-center mt-4'>Invalid appointment ID.</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND resident_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $id, $residentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-red-600 text-center mt-4'>Appointment not found or cannot be edited.</p>";
    exit;
}

$appt = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Appointment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 py-8">

  <div class="w-full max-w-xl bg-white p-6 rounded-xl shadow-md border border-blue-200">
    <h2 class="text-2xl font-semibold text-center text-blue-700 mb-6">Edit Appointment</h2>

    <form method="POST" action="update_appointment.php" class="space-y-4">
      <input type="hidden" name="id" value="<?= $appt['id'] ?>">

      <!-- 📅 Date -->
      <div>
        <label for="appointment_date" class="block mb-1 font-medium text-sm text-gray-700">Appointment Date</label>
        <input type="date" name="appointment_date" id="appointment_date"
               value="<?= $appt['appointment_date'] ?>" required
               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- ⏰ Time -->
      <div>
        <label for="appointment_time" class="block mb-1 font-medium text-sm text-gray-700">Appointment Time</label>
        <input type="time" name="appointment_time" id="appointment_time"
               value="<?= $appt['appointment_time'] ?>" required
               class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- 📝 Purpose -->
      <div>
        <label for="purpose" class="block mb-1 font-medium text-sm text-gray-700">Purpose</label>
        <textarea name="purpose" id="purpose" rows="4" required
                  class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($appt['purpose']) ?></textarea>
      </div>
<div class="flex justify-center gap-3 pt-4">
  <button type="button"
          onclick="window.location.href='main.php?section=appointments'"
          class="inline-flex items-center bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded-full text-sm">
    Cancel
  </button>
  <button type="submit"
          class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-full text-sm">
    Update
  </button>
</div>



    </form>
  </div>

</body>
</html>
