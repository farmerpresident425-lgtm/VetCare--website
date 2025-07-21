<?php
include_once '../config/db.php';

// Query to count total animals
$sql = "SELECT COUNT(*) AS total_animals FROM animal_health";  // change to your actual table name
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Output the number
echo $row['total_animals'];
?>
