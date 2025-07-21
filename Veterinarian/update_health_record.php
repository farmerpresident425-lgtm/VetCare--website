<?php
session_name("vet_session");
session_start();

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Prepare and validate fields
    $animal_name = $_POST['animal_name'];
    $species = $_POST['species'];
    $diagnosis = $_POST['diagnosis'];
    $treatment_given = $_POST['treatment_given'];

    // You can add more validation here if needed

    // Update query
    $stmt = $conn->prepare("
        UPDATE animal_health_records 
        SET animal_name = ?, species = ?, diagnosis = ?, treatment_given = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $animal_name, $species, $diagnosis, $treatment_given, $id);

    if ($stmt->execute()) {
        // Optional: redirect to view page or back to main dashboard
        header("Location: main.php?section=records&success=record_updated");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
