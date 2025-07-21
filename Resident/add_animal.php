<?php
ob_start();

include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_SESSION['user_id'];
    $animal_name = $_POST['animal_name'];
    $breed = $_POST['breed'];
    $sex = $_POST['sex'];
    $color = $_POST['color'];
    $weight_kg = $_POST['weight_kg'];
    $identification_mark = $_POST['identification_mark'];
    $street_address = $_POST['street_address'];
    $municipality = $_POST['municipality'];
    $province = $_POST['province'];
    $phone_number = $_POST['phone_number'];

    $stmt = $conn->prepare("INSERT INTO animals (
        owner_id, animal_name, breed, sex, color, weight_kg, identification_mark,
        street_address, municipality, province, phone_number
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("issssdsssss", $owner_id, $animal_name, $breed, $sex, $color, $weight_kg,
        $identification_mark, $street_address, $municipality, $province, $phone_number);

    if ($stmt->execute()) {
        echo "<script>window.location.href = 'main.php?section=add_animal&success=1';</script>";
        exit;
    } else {
        echo "<p class='text-red-600 text-center mt-4'>Error: " . $stmt->error . "</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Animal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 px-4 pt-8 md:pt-20">
<div class="min-h-screen flex items-start justify-center bg-gray-50 py-10 px-4">
  <div class="w-full max-w-4xl px-2">
    <div class="bg-white w-full p-6 sm:p-10 rounded-2xl shadow-xl">

      <form method="POST" class="space-y-8">
        <h2 class="text-center text-gray-800 mb-10 text-lg sm:text-xl font-semibold tracking-wide">
  Animal Registration Form
</h2>


        <!-- 👇 Form Fields: 2 Columns -->
        <!-- Group Address, City, Phone Number -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
  <div>
    <label class="block text-sm font-medium text-gray-700">Street Address</label>
    <input type="text" name="street_address"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Municipality</label>
    <input type="text" name="municipality"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div class="md:col-span-2">
  <label class="block text-sm font-medium text-gray-700">Province</label>
    <input type="text" name="province"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
    <input type="tel" name="phone_number" required placeholder="09XX XXX XXXX"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
</div>

<!-- Animal Info Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
  <div>
    <label class="block text-sm font-medium text-gray-700">Animal Name</label>
    <input type="text" name="animal_name" required
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Breed</label>
    <input type="text" name="breed" required
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Sex</label>
    <select name="sex" required
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
      <option value="">Select</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select>
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Color</label>
    <input type="text" name="color" required
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
    <input type="number" step="0.01" name="weight_kg" required
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-gray-700">Identification Mark</label>
    <input type="text" name="identification_mark"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
  </div>
</div>


        <!-- Submit Button -->
        <div class="text-center mt-6">
          <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-full text-sm transition">
            Submit Registration
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
