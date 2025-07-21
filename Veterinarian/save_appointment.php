<?php 
include '../config/db.php';

$animalId = $_POST['animal_id'] ?? null;
$residentId = $_POST['resident_id'] ?? null;
$date = $_POST['appointment_date'] ?? '';
$time = $_POST['appointment_time'] ?? '';
$purpose = $_POST['purpose'] ?? '';

if (empty($date) || empty($time) || empty($purpose)) {
    die("Please provide all required information.");
}

if ($residentId !== 'all') {
    // Specific resident
    if (empty($animalId) || empty($residentId)) {
        die("Please select animal and resident.");
    }

    $insert = $conn->prepare("INSERT INTO appointments (animal_id, resident_id, appointment_date, appointment_time, purpose) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("iisss", $animalId, $residentId, $date, $time, $purpose);
    $insert->execute();
    $insert->close();

} else {
    // All approved residents
    $resQuery = $conn->query("SELECT id FROM users WHERE role = 'resident' AND status = 'approved'");

    while ($res = $resQuery->fetch_assoc()) {
        $resId = $res['id'];

        // Get all animals for each resident
        $stmt = $conn->prepare("SELECT id FROM animals WHERE owner_id = ?");
        $stmt->bind_param("i", $resId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($animal = $result->fetch_assoc()) {
            $animalId = $animal['id'];

            $save = $conn->prepare("INSERT INTO appointments (animal_id, resident_id, appointment_date, appointment_time, purpose) VALUES (?, ?, ?, ?, ?)");
            $save->bind_param("iisss", $animalId, $resId, $date, $time, $purpose);
            $save->execute();
            $save->close();
        }
    }
}

header("Location: main.php?section=appointments&success=1");
exit;
