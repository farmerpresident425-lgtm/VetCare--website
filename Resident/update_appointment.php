<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['id'];
    $residentId = $_SESSION['user_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $purpose = $_POST['purpose'];

    $stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, purpose = ? WHERE id = ? AND resident_id = ?");
    $stmt->bind_param("sssii", $date, $time, $purpose, $appointment_id, $residentId);

    if ($stmt->execute()) {
        header("Location: main.php?section=appointments");
        exit;
    } else {
        echo "Error updating appointment: " . $stmt->error;
    }
}
?>
