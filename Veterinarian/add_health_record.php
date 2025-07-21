<?php
include '../config/db.php';

$residentId = $_GET['resident_id'] ?? null;
if (!$residentId) {
    echo "Invalid resident.";
    exit;
}

// 🐾 Fetch resident name
$res_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$res_stmt->bind_param("i", $residentId);
$res_stmt->execute();
$res_result = $res_stmt->get_result();
$res_name = $res_result->fetch_assoc()['name'] ?? "Unknown";

// ✅ Save form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO animal_health_records (
    owner_id, animal_name, breed, sex, birth_date, color, weight_kg,
    date_of_checkup, medication_prescribed, dosage, next_checkup_date,
    vaccine_administered, remarks, recorded_by
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("issssssssssssi",
    $residentId,
    $_POST['animal_name'],
    $_POST['breed'],
    $_POST['sex'],
    $_POST['birth_date'],
    $_POST['color'],
    $_POST['weight_kg'],
    $_POST['date_of_checkup'],
    $_POST['medication_prescribed'],
    $_POST['dosage'],
    $_POST['next_checkup_date'],
    $_POST['vaccine_administered'],
    $_POST['remarks'],
    $_SESSION['user_id']
);





    if ($stmt->execute()) {
        echo "<script>alert('Record saved!'); window.location.href='main.php?section=view_health_records&resident_id=$residentId';</script>";
        exit;
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}
?>

<div class="max-w-4xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-md border border-gray-300">
  <h2 class="text-2xl font-bold text-center mb-6"> Animal Health Record for <span class="text-blue-600"><?= htmlspecialchars($res_name) ?></span></h2>

  <form method="POST" class="grid grid-cols-2 gap-6 text-sm">
    <div>
      <label class="block font-medium mb-1">Animal Name</label>
      <input type="text" name="animal_name" required class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Breed</label>
      <input type="text" name="breed" required class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Sex</label>
      <select name="sex" required class="border p-2 rounded w-full">
        <option value="">Select</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
    </div>
    <div>
      <label class="block font-medium mb-1">Birth Date</label>
      <input type="date" name="birth_date" required class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Color</label>
      <input type="text" name="color" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Weight (kg)</label>
      <input type="number" step="0.1" name="weight_kg" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Date of Check-up</label>
      <input type="date" name="date_of_checkup" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Medication Prescribed</label>
      <input type="text" name="medication_prescribed" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Dosage</label>
      <input type="text" name="dosage" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Next Checkup</label>
      <input type="date" name="next_checkup_date" class="border p-2 rounded w-full">
    </div>
    <div>
      <label class="block font-medium mb-1">Vaccine Administered?</label>
      <select name="vaccine_administered" required class="border p-2 rounded w-full">
        <option value="">Select</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
      </select>
    </div>
    <div class="col-span-2">
      <label class="block font-medium mb-1">Remarks</label>
      <textarea name="remarks" rows="3" class="border p-2 rounded w-full"></textarea>
    </div>

    <div class="col-span-2 flex justify-center mt-4">
      <button type="submit"
              class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
        Save Record
      </button>
    </div>
  </form>
</div>
