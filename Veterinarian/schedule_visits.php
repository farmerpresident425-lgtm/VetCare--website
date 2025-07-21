<?php include '../config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Schedule Visits</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

  <header class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Schedule Veterinary Visits</h1>
    <a href="veterinarian.php" class="flex items-center gap-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
      <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Dashboard
    </a>
  </header>

  <main class="bg-white rounded shadow-md p-6">
    <!-- TODO: insert calendar and scheduling UI -->
    <p class="text-gray-500">Here you can set and manage veterinary visit appointments.</p>
  </main>

  <script>lucide.createIcons();</script>
</body>
</html>
