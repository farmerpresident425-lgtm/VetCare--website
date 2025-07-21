<?php
include('../config/db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_name = $_POST['animal_name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $condition = $_POST['condition'];
    $last_checkup = $_POST['last_checkup'];

    $sql = "INSERT INTO animal_records (animal_name, species, breed, age, `condition`, last_checkup)
            VALUES ('$animal_name', '$species', '$breed', '$age', '$condition', '$last_checkup')";
    mysqli_query($conn, $sql);
    header("Location: view.php");
}
?>

<h3>Add Animal Record</h3>
<form method="POST">
    <input type="text" name="animal_name" class="form-control" placeholder="Animal Name" required><br>
    <input type="text" name="species" class="form-control" placeholder="Species" required><br>
    <input type="text" name="breed" class="form-control" placeholder="Breed" required><br>
    <input type="number" name="age" class="form-control" placeholder="Age" required><br>
    <textarea name="condition" class="form-control" placeholder="Health Condition" required></textarea><br>
    <input type="date" name="last_checkup" class="form-control" required><br>
    <button type="submit" class="btn btn-success">Save</button>
</form>
