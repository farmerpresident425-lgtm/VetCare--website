<?php
include '../config/db.php'; // adjust path if needed

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No record ID provided.";
    exit;
}

// Delete the record
$stmt = $conn->prepare("DELETE FROM animal_health_records WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirect back after deletion
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
} else {
    echo "Failed to delete the record.";
}
?>
