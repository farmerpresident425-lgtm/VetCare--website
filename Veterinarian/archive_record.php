<?php
include '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE animal_health_records SET status = 'archived' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: main.php?section=archived_records&archived=success");
        exit;
    } else {
        echo "Failed to archive record.";
    }
} else {
    echo "No record ID provided.";
}
?>
