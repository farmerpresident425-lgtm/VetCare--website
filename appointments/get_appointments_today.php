<?php
// Connect to your database
require '../config/db.php';
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get current date
$today = date('Y-m-d');

// Query to count today's appointments
$sql = "SELECT COUNT(*) AS total_today FROM appointments WHERE appointment_date = '$today'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Return count
echo $row['total_today'];

$conn->close();
?>
