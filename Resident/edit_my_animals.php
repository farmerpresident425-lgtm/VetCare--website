<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Animal ID is missing.";
    exit;
}

$animal_id = $_GET['id'];
$resident_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE animals SET animal_name=?, breed=?, sex=?, color=?, province=?, weight_kg=?, identification_mark=?, street_address=?, municipality=?, phone_number=? WHERE id=? AND owner_id=?");

    $stmt->bind_param("ssssssssssii",
        $_POST['animal_name'], $_POST['breed'], $_POST['sex'],  $_POST['province'], $_POST['color'],
        $_POST['weight_kg'], $_POST['identification_mark'], $_POST['street_address'],
        $_POST['municipality'],
        $_POST['phone_number'], $animal_id, $resident_id
    );

    if ($stmt->execute()) {
        header("Location: main.php?section=my_animals");
        exit;
    } else {
        echo "Error updating animal: " . $stmt->error;
    }
}

$stmt = $conn->prepare("SELECT * FROM animals WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $animal_id, $resident_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Animal not found.";
    exit;
}

$animal = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Animal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 py-8">

  <div class="w-full max-w-2xl bg-white p-6 rounded-lg shadow border border-blue-200">
    <h2 class="text-2xl font-semibold text-center text-blue-700 mb-6">Edit Animal Information</h2>

    <form method="POST" class="space-y-4 text-sm">
      <!-- 🏡 Address Section -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <!-- Street Address -->
  <div>
    <label class="block font-medium mb-1 text-gray-700">Street Address</label>
    <input type="text" name="street_address" 
           value="<?= htmlspecialchars($animal['street_address']) ?>"
           class="w-full border px-3 py-2 rounded focus:ring-blue-500 focus:outline-none" 
           required>
  </div>

  <!-- Municipality -->
  <div>
    <label class="block font-medium mb-1 text-gray-700">Municipality</label>
    <input type="text" name="municipality" 
           value="<?= htmlspecialchars($animal['municipality']) ?>"
           class="w-full border px-3 py-2 rounded focus:ring-blue-500 focus:outline-none" 
           required>
  </div>

  <!-- Province -->
  <div>
    <label class="block font-medium mb-1 text-gray-700">Province</label>
    <input type="text" name="province" 
           value="<?= htmlspecialchars($animal['province']) ?>"
           class="w-full border px-3 py-2 rounded focus:ring-blue-500 focus:outline-none" 
           required>
  </div>



      <!-- ☎️ Phone -->
      <div>
        <label class="block font-medium mb-1 text-gray-700">Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($animal['phone_number']) ?>"
               class="w-full border px-3 py-2 rounded" required>
      </div>

      <!-- 🐾 Animal Info -->
      <div>
        <label class="block font-medium mb-1 text-gray-700">Animal Name</label>
        <input type="text" name="animal_name" value="<?= htmlspecialchars($animal['animal_name']) ?>"
               class="w-full border px-3 py-2 rounded" required>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium mb-1 text-gray-700">Breed</label>
          <input type="text" name="breed" value="<?= htmlspecialchars($animal['breed']) ?>"
                 class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
          <label class="block font-medium mb-1 text-gray-700">Sex</label>
          <select name="sex" class="w-full border px-3 py-2 rounded">
            <option value="Male" <?= $animal['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $animal['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium mb-1 text-gray-700">Color</label>
          <input type="text" name="color" value="<?= htmlspecialchars($animal['color']) ?>"
                 class="w-full border px-3 py-2 rounded" required>
        </div>

        <div>
          <label class="block font-medium mb-1 text-gray-700">Weight (kg)</label>
          <input type="number" step="0.01" name="weight_kg" value="<?= htmlspecialchars($animal['weight_kg']) ?>"
                 class="w-full border px-3 py-2 rounded" required>
        </div>
      </div>

      <div>
        <label class="block font-medium mb-1 text-gray-700">Identification Mark</label>
        <input type="text" name="identification_mark" value="<?= htmlspecialchars($animal['identification_mark']) ?>"
               class="w-full border px-3 py-2 rounded">
      </div>

      <!-- 🔘 Buttons -->
<div class="flex justify-center gap-3 pt-6">
  <button type="button" onclick="window.location.href='main.php?section=my_animals'"
          class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 text-sm rounded">
    Cancel
  </button>
  <button type="submit"
          class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 text-sm rounded">
    Update
  </button>

</div>

      </div>
    </form>
  </div>

</body>
</html>
