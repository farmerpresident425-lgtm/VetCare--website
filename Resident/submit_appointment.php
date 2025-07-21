<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    echo "Unauthorized";
    exit;
}

$residentId = $_SESSION['user_id'];
$animal_id = $_POST['animal_id'] ?? null;
$date = $_POST['appointment_date'] ?? null;
$time = $_POST['appointment_time'] ?? null;
$purpose = trim($_POST['purpose'] ?? '');

if (!$animal_id || !$date || !$time || $purpose === '') {
    echo "Missing fields";
    exit;
}

// Insert the appointment
$stmt = $conn->prepare("INSERT INTO appointments (resident_id, animal_id, appointment_date, appointment_time, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("iisss", $residentId, $animal_id, $date, $time, $purpose);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Failed to submit.";
}
?>
