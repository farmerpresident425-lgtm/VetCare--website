<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vet') {
    header("Location: ../../index.php");
    exit;
}

$vetId = $_SESSION['user_id'] ?? 0;

// Fetch reports
$filterName = $_GET['resident'] ?? '';
$filterDate = $_GET['date'] ?? '';

$query = "
    SELECT ahr.*, u.name AS resident_name 
    FROM animal_health_records ahr
    JOIN users u ON ahr.resident_id = u.id
    WHERE 1
";

if ($filterName) {
    $query .= " AND u.name LIKE '%$filterName%'";
}
if ($filterDate) {
    $query .= " AND DATE(ahr.date_of_checkup) = '$filterDate'";
}

$query .= " ORDER BY ahr.date_of_checkup DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vet Reports</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">📊 Animal Health Reports</h1>

    <!-- Filters -->
    <form method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
      <input type="text" name="resident" placeholder="Search Resident" value="<?= htmlspecialchars($filterName) ?>" class="border p-2 rounded w-full md:w-1/3">
      <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" class="border p-2 rounded w-full md:w-1/3">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
      <a href="vet_reports.php" class="bg-gray-400 text-white px-4 py-2 rounded">Reset</a>
      <button onclick="window.print()" type="button" class="bg-green-600 text-white px-4 py-2 rounded">🖨️ Print</button>
    </form>

    <!-- Report Table -->
    <div class="overflow-auto">
      <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="border px-4 py-2">Resident</th>
            <th class="border px-4 py-2">Animal Name</th>
            <th class="border px-4 py-2">Type</th>
            <th class="border px-4 py-2">Diagnosis</th>
            <th class="border px-4 py-2">Treatment</th>
            <th class="border px-4 py-2">Checkup Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-100">
                <td class="border px-4 py-2"><?= htmlspecialchars($row['resident_name']) ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($row['animal_name']) ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($row['animal_type']) ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($row['diagnosis']) ?></td>
                <td class="border px-4 py-2"><?= htmlspecialchars($row['treatment']) ?></td>
                <td class="border px-4 py-2"><?= date('M d, Y', strtotime($row['date_of_checkup'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-gray-500">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
