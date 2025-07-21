<?php
// Fetch residents
$residents = $conn->query("SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");

// Fetch all animals with their owner
$animalQuery = $conn->query("
  SELECT a.id, a.name AS animal_name, a.species, u.name AS owner_name
  FROM animals a
  JOIN users u ON a.owner_id = u.id
");
?>

<h2 class="text-xl font-bold mb-4">Animal List & Add Animal</h2>

<form method="POST" action="save_animal.php" class="bg-gray-50 p-4 rounded-lg shadow mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
  <div>
    <label class="block mb-1">Resident</label>
    <select name="owner_id" required class="w-full border px-3 py-2 rounded">
      <option value="">-- Select Resident --</option>
      <?php while($res = $residents->fetch_assoc()): ?>
        <option value="<?= $res['id'] ?>"><?= htmlspecialchars($res['name']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div>
    <label class="block mb-1">Animal Name</label>
    <input type="text" name="animal_name" required class="w-full border px-3 py-2 rounded">
  </div>

  <div>
    <label class="block mb-1">Species</label>
    <input type="text" name="species" required class="w-full border px-3 py-2 rounded">
  </div>

  <div class="md:col-span-3 text-right">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
       Add Animal
    </button>
  </div>
</form>

<!-- Table of animals -->
<div class="overflow-x-auto">
  <table class="min-w-full bg-white shadow rounded-lg overflow-hidden">
    <thead class="bg-gray-200 text-gray-600 uppercase text-sm">
      <tr>
        <th class="py-3 px-4 text-left">Animal Name</th>
        <th class="py-3 px-4 text-left">Species</th>
        <th class="py-3 px-4 text-left">Owner</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $animalQuery->fetch_assoc()): ?>
        <tr class="border-t hover:bg-gray-50">
          <td class="py-2 px-4"><?= htmlspecialchars($row['animal_name']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($row['species']) ?></td>
          <td class="py-2 px-4"><?= htmlspecialchars($row['owner_name']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
