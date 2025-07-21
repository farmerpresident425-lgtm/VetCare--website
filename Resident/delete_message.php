<?php
include '../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Message ID missing"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Message deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete"]);
}
?>
