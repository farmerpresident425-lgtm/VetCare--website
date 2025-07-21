<?php  
include '../config/db.php';

$resident_id = $_GET['resident_id'] ?? null;

if (!$resident_id) {
    echo "No resident selected.";
    exit;
}

// Get resident name for heading
$res_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$res_stmt->bind_param("i", $resident_id);
$res_stmt->execute();
$res_result = $res_stmt->get_result();
$res_name = $res_result->fetch_assoc()['name'] ?? "Unknown";

// Fetch animal health records
$stmt = $conn->prepare("SELECT * FROM animal_health_records WHERE owner_id = ?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 class="text-xl font-semibold mb-4">Animal Health Records for <?= htmlspecialchars($res_name) ?></h2>

<div class="overflow-x-auto">
  <table class="min-w-full table-auto border border-gray-300 text-sm shadow-sm">
    <thead class="bg-gray-100 text-gray-700">
      <tr>
        <th class="border px-4 py-2">Animal Name</th>
        <th class="border px-4 py-2">Breed</th>
        <th class="border px-4 py-2">Sex</th>
        <th class="border px-4 py-2">Birth Date</th>
        <th class="border px-4 py-2">Color</th>
        <th class="border px-4 py-2">Weight (kg)</th>
        <th class="border px-4 py-2">Date of Check-up</th>
        <th class="border px-4 py-2">Medication Prescribed</th>
        <th class="border px-4 py-2">Dosage</th>
        <th class="border px-4 py-2">Next Checkup</th>
        <th class="border px-4 py-2">Vaccine Administered?</th>
        <th class="border px-4 py-2">Remarks</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50">
          <td class="border px-4 py-2"><?= htmlspecialchars($row['animal_name']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['breed']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['sex']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['birth_date']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['color']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['weight_kg']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['date_of_checkup']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['medication_prescribed']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['dosage']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['next_checkup_date']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['vaccine_administered']) ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['remarks']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
