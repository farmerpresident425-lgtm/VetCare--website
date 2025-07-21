<?php
require '../config/db.php';

$q = $_GET['q'] ?? '';
$q = trim($q);

$stmt = $conn->prepare("
    SELECT id, name, role 
    FROM users 
    WHERE status = 'approved' 
    AND name LIKE CONCAT(?, '%')
");
$stmt->bind_param("s", $q);
$stmt->execute();
$res = $stmt->get_result();

$results = [];
while ($row = $res->fetch_assoc()) {
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);
?>
