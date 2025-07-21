<?php
include '../config/db.php';

$owner_id = $_POST['owner_id'];
$animal_name = trim($_POST['animal_name']);
$species = trim($_POST['species']);

if ($owner_id && $animal_name && $species) {
    $stmt = $conn->prepare("INSERT INTO animals (owner_id, name, species) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $owner_id, $animal_name, $species);
    $stmt->execute();
    $stmt->close();
}

header("Location: main.php?section=animals");
exit;
