<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Optional: use this to limit access
function checkRole($role) {
    if ($_SESSION['role'] !== $role) {
        header("Location: ../index.php");
        exit();
    }
}
?>
