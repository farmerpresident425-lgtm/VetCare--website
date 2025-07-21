<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['animal_id'])) {
    $animalId = $_POST['animal_id'];
    $residentId = $_SESSION['user_id'];

    // Double-check ownership before deletion
    $stmt = $conn->prepare("DELETE FROM animals WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $animalId, $residentId);

    if ($stmt->execute()) {
        header("Location: main.php?section=my_animals&success=deleted");
        exit;
    } else {
        echo "Error deleting animal: " . $stmt->error;
    }
}
?>
