<?php
include('../config/db.php');
$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM animal_records WHERE id=$id");
header("Location: view.php");
