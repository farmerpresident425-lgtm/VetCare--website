<?php
include '../config/db.php';
session_name("vet_session");
session_start();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: main.php?section=appointments");
    exit;
}

$residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");
$animals = $conn->query("SELECT id, name FROM animals");

$appointment = $conn->query("SELECT * FROM appointments WHERE id = $id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_id = $_POST['animal_id'];
    $resident_id = $_POST['resident_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $purpose = $_POST['purpose'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE appointments SET animal_id = ?, resident_id = ?, appointment_date = ?, appointment_time = ?, purpose = ?, status = ? WHERE id = ?");
    $stmt->bind_param("iissssi", $animal_id, $resident_id, $date, $time, $purpose, $status, $id);
    $stmt->execute();

    header("Location: main.php?section=appointments");
    exit;
}
?>

<div class="p-4">
  <h2 class="text-xl font-semibold mb-4">Edit Appointment</h2>
  <form method="POST" class="space-y-4">
    <div>
      <label>Resident:</label>
      <select name="resident_id" required class="border rounded px-2 py-1 w-full">
        <?php while ($r = $residents->fetch_assoc()): ?>
          <option value="<?= $r['id'] ?>" <?= $r['id'] == $appointment['resident_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>Animal:</label>
      <select name="animal_id" required class="border rounded px-2 py-1 w-full">
        <?php while ($a = $animals->fetch_assoc()): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id'] == $appointment['animal_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div>
      <label>Date:</label>
      <input type="date" name="appointment_date" value="<?= $appointment['appointment_date'] ?>" required class="border rounded px-2 py-1 w-full">
    </div>

    <div>
      <label>Time:</label>
      <input type="time" name="appointment_time" value="<?= $appointment['appointment_time'] ?>" required class="border rounded px-2 py-1 w-full">
    </div>

    <div>
      <label>Purpose:</label>
      <textarea name="purpose" class="border rounded px-2 py-1 w-full"><?= htmlspecialchars($appointment['purpose']) ?></textarea>
    </div>

    <div>
      <label>Status:</label>
      <select name="status" class="border rounded px-2 py-1 w-full">
        <option value="pending" <?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $appointment['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>

    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">Update</button>
  </form>
</div>
