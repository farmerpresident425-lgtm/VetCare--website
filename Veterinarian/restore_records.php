<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO animal_health_records 
        (owner_id, animal_name, species, breed, sex, birth_date, color, weight_kg, identification_mark,
        date_of_checkup, diagnosis, treatment_given, medication_prescribed, dosage, next_checkup_date,
        vaccine_administered, vaccine_type, remarks, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("isssssssdssssssssssi",
        $_POST['owner_id'], $_POST['animal_name'], $_POST['species'], $_POST['breed'], $_POST['sex'],
        $_POST['birth_date'], $_POST['color'], $_POST['weight_kg'], $_POST['identification_mark'],
        $_POST['date_of_checkup'], $_POST['diagnosis'], $_POST['treatment_given'], $_POST['medication_prescribed'],
        $_POST['dosage'], $_POST['next_checkup_date'], $_POST['vaccine_administered'],
        $_POST['vaccine_type'], $_POST['remarks'], $_POST['recorded_by']
    );

    if ($stmt->execute()) {
        echo "Animal health record added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
