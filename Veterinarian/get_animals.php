<?php
include '../config/db.php';

$residentId = $_GET['resident_id'] ?? null;

if ($residentId) {
    $stmt = $conn->prepare("SELECT id, name FROM animals WHERE owner_id = ?");
    $stmt->bind_param("i", $residentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $animals = [];
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($animals);
} else {
    echo json_encode([]);
}
