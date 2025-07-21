<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("UPDATE animal_health_records SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: main.php?section=archived_records");
exit;
