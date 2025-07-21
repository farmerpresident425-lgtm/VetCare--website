<?php
include '../config/db.php';

// Handle filters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$residentId = $_GET['resident_id'] ?? '';

// Build filter condition
$filter = "WHERE 1=1";
$params = [];

if (!empty($startDate)) {
    $filter .= " AND date_of_checkup >= ?";
    $params[] = $startDate;
}
if (!empty($endDate)) {
    $filter .= " AND date_of_checkup <= ?";
    $params[] = $endDate;
}
if (!empty($residentId)) {
    $filter .= " AND owner_id = ?";
    $params[] = $residentId;
}

// Prepare dynamic query
$query = "SELECT breed as species, COUNT(*) as count
FROM animal_health_records
$filter
GROUP BY species
";
$stmt = $conn->prepare($query);

// Bind parameters dynamically
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$results = $stmt->get_result();
?>

<h2 class="text-2xl font-bold mb-4">📈 Veterinary Reports</h2>

<!-- ✅ Filter Form with Correct Action -->
<form method="GET" action="main.php" class="mb-6 flex flex-wrap gap-4 items-end">
    <input type="hidden" name="section" value="reports">
    
    <div>
        <label class="block text-sm font-semibold">Start Date:</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="border p-2 rounded w-full">
    </div>
    <div>
        <label class="block text-sm font-semibold">End Date:</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="border p-2 rounded w-full">
    </div>
    <div>
        <label class="block text-sm font-semibold">Resident (optional):</label>
        <select name="resident_id" class="border p-2 rounded w-full">
            <option value="">All Residents</option>
            <?php
            $resQuery = $conn->query("SELECT id, name FROM users WHERE role = 'resident'");
            while ($res = $resQuery->fetch_assoc()):
            ?>
                <option value="<?= $res['id'] ?>" <?= $residentId == $res['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($res['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </div>
</form>

<!-- Report Table -->
<div class="bg-white rounded shadow p-4">
    <?php if ($results->num_rows > 0): ?>
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2 border">Animal Type</th>
                    <th class="p-2 border">Total Records</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $results->fetch_assoc()): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($row['species']) ?></td>
                        <td class="p-2 border"><?= $row['count'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-600">No records found for the selected filters.</p>
    <?php endif; ?>
</div>
