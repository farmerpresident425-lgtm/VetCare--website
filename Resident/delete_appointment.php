<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

$appointmentId = $_GET['id'] ?? 0;
$residentId = $_SESSION['user_id'];

// Validation
if (!is_numeric($appointmentId) || $appointmentId <= 0) {
    echo "<p class='text-red-600 text-center mt-4'>Invalid appointment ID.</p>";
    exit;
}

// Delete only if appointment is owned by the resident and still pending
$stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND resident_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $appointmentId, $residentId);

if ($stmt->execute()) {
    header("Location: main.php?section=appointments");
    exit;
} else {
    echo "<p class='text-red-600 text-center mt-4'>Failed to delete the appointment.</p>";
}
?>
