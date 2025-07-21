<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Animal ID is missing.";
    exit;
}

$animalId = intval($_GET['id']);
$residentId = $_SESSION['user_id'];

// Protect: only delete if the animal belongs to the logged-in user
$stmt = $conn->prepare("DELETE FROM animals WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $animalId, $residentId);

if ($stmt->execute()) {
    header("Location: main.php?section=my_animals");
    exit;
} else {
    echo "Error deleting animal: " . $stmt->error;
}
?>
