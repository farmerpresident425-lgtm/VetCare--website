<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['receiver_id'])) {
    exit(json_encode([]));
}

$admin_id = $_SESSION['user_id'];
$resident_id = (int) $_GET['receiver_id'];

$stmt = $conn->prepare("SELECT sender_id, receiver_id, message FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC");
$stmt->bind_param("iiii", $admin_id, $resident_id, $resident_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
?>
