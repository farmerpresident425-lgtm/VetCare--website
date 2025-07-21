<?php 

include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

$residentId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, animal_name, breed, sex, color, weight_kg, identification_mark, municipality, street_address, province, phone_number FROM animals WHERE owner_id = ? ORDER BY animal_name ASC");
$stmt->bind_param("i", $residentId);
$stmt->execute();
$animals = $stmt->get_result();
$animalRows = $animals->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Registered Animals</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-4 sm:p-6 text-gray-800">

<?php if (!empty($animalRows)): ?>
<!-- ✅ Desktop/Tablet Table View -->
<div class="hidden md:block overflow-x-auto">
  <table class="w-full min-w-[1000px] bg-white rounded-lg shadow text-sm">
    <thead class="bg-yellow-100 text-yellow-900">
      <tr>
        <th class="px-6 py-3 text-left">Street Address</th>
        <th class="px-6 py-3 text-left">Municipality</th>
        <th class="px-6 py-3 text-left">Province</th>
        <th class="px-6 py-3 text-left">Phone</th>
        <th class="px-6 py-3 text-left">Animal Name</th>
        <th class="px-6 py-3 text-left">Breed</th>
        <th class="px-6 py-3 text-left">Sex</th>
        <th class="px-6 py-3 text-left">Color</th>
        <th class="px-6 py-3 text-left">Weight</th>
        <th class="px-6 py-3 text-left">ID Mark</th>
        <th class="px-6 py-3 text-left">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($animalRows as $row): ?>
        <tr class="border-t hover:bg-gray-50">
          <td class="px-6 py-3"><?= htmlspecialchars($row['street_address']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['municipality']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['province']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['phone_number']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['animal_name']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['breed']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['sex']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['color']) ?></td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['weight_kg']) ?> kg</td>
          <td class="px-6 py-3"><?= htmlspecialchars($row['identification_mark']) ?></td>
          <td class="px-6 py-3">
            <div class="flex flex-col sm:flex-row gap-2">
              <a href="edit_my_animals.php?id=<?= $row['id'] ?>" class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 text-xs">Edit</a>
              <a href="delete_my_animals.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 text-xs">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ✅ Mobile Card View -->
<div class="md:hidden mt-6">
  <div class="bg-white p-4 rounded-xl shadow max-w-2xl mx-auto space-y-4">
    <?php foreach ($animalRows as $row): ?>
    <div class="border border-gray-200 rounded-lg p-4 text-sm">
      <div><span class="font-semibold">Animal Name:</span> <?= htmlspecialchars($row['animal_name']) ?></div>
      <div><span class="font-semibold">Breed:</span> <?= htmlspecialchars($row['breed']) ?></div>
      <div><span class="font-semibold">Sex:</span> <?= htmlspecialchars($row['sex']) ?></div>
      <div><span class="font-semibold">Color:</span> <?= htmlspecialchars($row['color']) ?></div>
      <div><span class="font-semibold">Weight:</span> <?= htmlspecialchars($row['weight_kg']) ?> kg</div>
      <div><span class="font-semibold">Identification Mark:</span> <?= htmlspecialchars($row['identification_mark']) ?></div>
      <div><span class="font-semibold">Street Address:</span> <?= htmlspecialchars($row['street_address']) ?></div>
      <div><span class="font-semibold">Municipality:</span> <?= htmlspecialchars($row['municipality']) ?></div>
      <div><span class="font-semibold">Province:</span>
       <?= htmlspecialchars($row['province']) ?></div>
      <div><span class="font-semibold">Phone #:</span> <?= htmlspecialchars($row['phone_number']) ?></div>
      <div class="flex justify-start gap-2 mt-3">
        <a href="edit_my_animals.php?id=<?= $row['id'] ?>" class="bg-blue-100 text-blue-700 px-3 py-1 text-xs rounded hover:bg-blue-200">Edit</a>
        <a href="delete_my_animals.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 text-xs rounded">Delete</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php else: ?>
  <p class="text-center text-red-600 mt-6">You haven't registered any animals yet.</p>
<?php endif; ?>

</body>
</html>
