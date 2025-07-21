<?php
require '../config/db.php';

$result = mysqli_query($conn, "SELECT * FROM animal_records");
?>

<h3>Animal Health Records</h3>
<a href="create.php" class="btn btn-success mb-2">Add New Record</a>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Animal Name</th>
            <th>Species</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Condition</th>
            <th>Last Checkup</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= htmlspecialchars($row['animal_name']) ?></td>
            <td><?= htmlspecialchars($row['species']) ?></td>
            <td><?= htmlspecialchars($row['breed']) ?></td>
            <td><?= $row['age'] ?></td>
            <td><?= htmlspecialchars($row['condition']) ?></td>
            <td><?= $row['last_checkup'] ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
