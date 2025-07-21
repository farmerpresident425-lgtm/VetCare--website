<?php
include '../config/db.php';

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

if ($id && in_array($action, ['approve', 'cancel', 'complete'])) {
  $status = match($action) {
    'approve' => 'approved',
    'cancel' => 'cancelled',
    'complete' => 'completed',
  };

  $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $status, $id);
  $stmt->execute();
}

header("Location: appointments.php");
exit;
