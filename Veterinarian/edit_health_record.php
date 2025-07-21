<?php
include '../config/db.php';

$record_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$record_id) {
    echo "No record selected.";
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE animal_health_records SET
    breed = ?, sex = ?, birth_date = ?, color = ?, weight_kg = ?,
    identification_mark = ?, date_of_checkup = ?, diagnosis = ?, treatment_given = ?,
    medication_prescribed = ?, dosage = ?, next_checkup_date = ?, vaccine_administered = ?,
    vaccine_type = ?, remarks = ? WHERE id = ?");

$stmt->bind_param("ssssdssssssssssi",
    $_POST['breed'],
    $_POST['sex'],
    $_POST['birth_date'],
    $_POST['color'],
    $_POST['weight_kg'],
    $_POST['identification_mark'],
    $_POST['date_of_checkup'],
    $_POST['diagnosis'],
    $_POST['treatment_given'],
    $_POST['medication_prescribed'],
    $_POST['dosage'],
    $_POST['next_checkup_date'],
    $_POST['vaccine_administered'],
    $_POST['vaccine_type'],
    $_POST['remarks'],
    $_POST['id']
);


    if ($stmt->execute()) {
        // Redirect using POST record's owner_id if available
        $owner_stmt = $conn->prepare("SELECT owner_id FROM animal_health_records WHERE id = ?");
        $owner_stmt->bind_param("i", $_POST['id']);
        $owner_stmt->execute();
        $owner_result = $owner_stmt->get_result();
        $owner_id = $owner_result->fetch_assoc()['owner_id'] ?? null;

        if ($owner_id) {
            header("Location: main.php?section=view_health_records&resident_id=" . $owner_id);
            exit;
        } else {
            echo "Update success, but owner not found.";
        }
    } else {
        echo "Update failed: " . $stmt->error;
    }
}

// Fetch existing data
$stmt = $conn->prepare("SELECT ahr.*, u.name AS owner_name FROM animal_health_records ahr JOIN users u ON ahr.owner_id = u.id WHERE ahr.id = ?");
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    echo "Record not found.";
    exit;
}
?>

<!-- Tailwind Form UI -->
<div class="p-6 bg-white rounded shadow">
  <h2 class="text-2xl font-bold mb-2">Update <?= htmlspecialchars($record['owner_name']) ?> Animal Health Records</h2>

  <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <input type="hidden" name="id" value="<?= $record['id'] ?>">
    
    <input type="text" name="breed" value="<?= htmlspecialchars($record['breed']) ?>" class="border p-2 rounded" placeholder="Breed" required>
    <input type="text" name="sex" value="<?= htmlspecialchars($record['sex']) ?>" class="border p-2 rounded" placeholder="Sex" required>
    <input type="date" name="birth_date" value="<?= htmlspecialchars($record['birth_date']) ?>" class="border p-2 rounded">
    <input type="text" name="color" value="<?= htmlspecialchars($record['color']) ?>" class="border p-2 rounded" placeholder="Color">
    <input type="number" step="0.01" name="weight_kg" value="<?= htmlspecialchars($record['weight_kg']) ?>" class="border p-2 rounded" placeholder="Weight (kg)">
    <input type="text" name="identification_mark" value="<?= htmlspecialchars($record['identification_mark']) ?>" class="border p-2 rounded" placeholder="Identification Mark">
    <input type="date" name="date_of_checkup" value="<?= htmlspecialchars($record['date_of_checkup']) ?>" class="border p-2 rounded">
    <input type="text" name="diagnosis" value="<?= htmlspecialchars($record['diagnosis']) ?>" class="border p-2 rounded" placeholder="Diagnosis">
    <input type="text" name="treatment_given" value="<?= htmlspecialchars($record['treatment_given']) ?>" class="border p-2 rounded" placeholder="Treatment Given">
    <input type="text" name="medication_prescribed" value="<?= htmlspecialchars($record['medication_prescribed']) ?>" class="border p-2 rounded" placeholder="Medication Prescribed">
    <input type="text" name="dosage" value="<?= htmlspecialchars($record['dosage']) ?>" class="border p-2 rounded" placeholder="Dosage">
    <input type="date" name="next_checkup_date" value="<?= htmlspecialchars($record['next_checkup_date']) ?>" class="border p-2 rounded">
    <input type="text" name="vaccine_administered" value="<?= htmlspecialchars($record['vaccine_administered']) ?>" class="border p-2 rounded" placeholder="Vaccine Administered">
    <input type="text" name="vaccine_type" value="<?= htmlspecialchars($record['vaccine_type']) ?>" class="border p-2 rounded" placeholder="Vaccine Type">
    <textarea name="remarks" class="border p-2 rounded md:col-span-2" placeholder="Remarks"><?= htmlspecialchars($record['remarks']) ?></textarea>

    <div class="md:col-span-2 flex justify-between mt-4">
      <a href="main.php?section=view_health_records&resident_id=<?= $record['owner_id'] ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancel</a>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Record</button>
    </div>
  </form>
</div>
