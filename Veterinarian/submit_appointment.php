<?php
include '../config/db.php';

$id = $_POST['id'] ?? $_GET['id'];
$action = $_POST['action'] ?? $_GET['action'];

if ($action === 'approve') {
    $conn->query("UPDATE appointments SET status = 'approved' WHERE id = $id");
} elseif ($action === 'cancel') {
    $reason = $_POST['reason'] ?? 'No reason provided';
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled',  cancel_reason= ? WHERE id = ?");
    $stmt->bind_param("si", $reason, $id);
    $stmt->execute();
}

header("Location: main.php?section=appointment_request");
exit;
