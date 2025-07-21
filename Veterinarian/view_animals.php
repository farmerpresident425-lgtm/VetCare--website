<?php
$resident_id = $_GET['resident_id'] ?? 0;

if (!$resident_id) {
    echo "<p class='text-red-500'>Resident not found.</p>";
    return;
}

// Get resident info
$res = $conn->prepare("SELECT name FROM users WHERE id = ?");
$res->bind_param("i", $resident_id);
$res->execute();
$resident = $res->get_result()->fetch_assoc();

// Get animals
$stmt = $conn->prepare("SELECT street_address, municipality, province, phone_number, animal_name, breed, sex, color, weight_kg, identification_mark FROM animals WHERE owner_id = ?");
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 class="text-xl font-semibold mb-4">Animals Registered by <?= htmlspecialchars($resident['name']) ?></h2>

<?php if ($result->num_rows > 0): ?>

<!-- 🖥️ Table for Medium and Larger Screens -->
<div class="hidden md:block overflow-x-auto">
  <table class="w-full table-auto border border-gray-300 text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="border px-3 py-2">Street Address</th>
        <th class="border px-3 py-2">Municipality</th>
        <th class="border px-3 py-2">Province</th>
        <th class="border px-3 py-2">Phone Number</th>
        <th class="border px-3 py-2">Animal Name</th>
        <th class="border px-3 py-2">Breed</th>
        <th class="border px-3 py-2">Sex</th>
        <th class="border px-3 py-2">Color</th>
        <th class="border px-3 py-2">Weight</th>
        <th class="border px-3 py-2">Identification Mark</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      // Rewind result set to reuse for cards
      $result->data_seek(0);
      while ($animal = $result->fetch_assoc()): ?>
        <tr>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['street_address']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['municipality']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['province']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['phone_number']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['animal_name']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['breed']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['sex']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['color']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['weight_kg']) ?></td>
          <td class="border px-3 py-2"><?= htmlspecialchars($animal['identification_mark']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- 📱 Card View for Small Screens -->
<div class="md:hidden space-y-4">
  <?php 
  // Rewind result set to reuse for cards
  $result->data_seek(0);
  while ($animal = $result->fetch_assoc()): ?>
    <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
      <p><strong>Street:</strong> <?= htmlspecialchars($animal['street_address']) ?></p>
      <p><strong>Municipality:</strong> <?= htmlspecialchars($animal['municipality']) ?></p>
      <p><strong>Province:</strong> <?= htmlspecialchars($animal['province']) ?></p>
      <p><strong>Phone #:</strong> <?= htmlspecialchars($animal['phone_number']) ?></p>
      <p><strong>Animal Name:</strong> <?= htmlspecialchars($animal['animal_name']) ?></p>
      <p><strong>Breed:</strong> <?= htmlspecialchars($animal['breed']) ?></p>
      <p><strong>Sex:</strong> <?= htmlspecialchars($animal['sex']) ?></p>
      <p><strong>Color:</strong> <?= htmlspecialchars($animal['color']) ?></p>
      <p><strong>Weight:</strong> <?= htmlspecialchars($animal['weight_kg']) ?></p>
      <p><strong>Identification Mark:</strong> <?= htmlspecialchars($animal['identification_mark']) ?></p>
    </div>
  <?php endwhile; ?>
</div>

<?php else: ?>
  <p class="text-gray-600">This resident has no registered animals.</p>
<?php endif; ?>
