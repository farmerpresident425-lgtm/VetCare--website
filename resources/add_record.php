<?php
// add_record.php
include '../config/db.php'; // or your DB connection file

$animal_id = $_POST['animal_id'] ?? '';
$animal_name = $_POST['animal_name'] ?? '';
$owner_name = $_POST['owner_name'] ?? '';
$health_status = $_POST['health_status'] ?? '';
$last_checkup = $_POST['last_checkup'] ?? '';

if ($animal_id && $animal_name && $owner_name && $health_status && $last_checkup) {
    $stmt = $conn->prepare("INSERT INTO animal_records (animal_id, animal_name, owner_name, health_status, last_checkup) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $animal_id, $animal_name, $owner_name, $health_status, $last_checkup);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'record' => [
                'animal_id' => $animal_id,
                'animal_name' => $animal_name,
                'owner_name' => $owner_name,
                'health_status' => $health_status,
                'last_checkup' => $last_checkup
            ]
        ]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
