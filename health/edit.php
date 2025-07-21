<?php
include('../config/db.php');
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_name = $_POST['animal_name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $condition = $_POST['condition'];
    $last_checkup = $_POST['last_checkup'];

    $sql = "UPDATE animal_records SET 
                animal_name='$animal_name', 
                species='$species', 
                breed='$breed', 
                age='$age', 
                `condition`='$condition', 
                last_checkup='$last_checkup' 
            WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: view.php");
} else {
    $result = mysqli_query($conn, "SELECT * FROM animal_records WHERE id=$id");
    $data = mysqli_fetch_assoc($result);
}
?>

<h3>Edit Animal Record</h3>
<form method="POST">
    <input type="text" name="animal_name" class="form-control" value="<?= $data['animal_name'] ?>" required><br>
    <input type="text" name="species" class="form-control" value="<?= $data['species'] ?>" required><br>
    <input type="text" name="breed" class="form-control" value="<?= $data['breed'] ?>" required><br>
    <input type="number" name="age" class="form-control" value="<?= $data['age'] ?>" required><br>
    <textarea name="condition" class="form-control" required><?= $data['condition'] ?></textarea><br>
    <input type="date" name="last_checkup" class="form-control" value="<?= $data['last_checkup'] ?>" required><br>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
