<?php 
include_once '../config/db.php';

$result = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'resident' AND status = 'approved'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approved Residents</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100 min-h-screen">
  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-bold">Approved Residents</h1>
       <a href="veterinarian.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition flex items-center gap-2">
        <!-- Left Arrow Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </a>
    </div>

    <table class="w-full table-auto border border-gray-300 text-sm">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 border">Resident Name</th>
          <th class="px-4 py-2 border">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
            <td class="border px-4 py-2 text-center">
              <a href="add_health_record.php?resident_id=<?= $row['id'] ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                Add Health Record
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
