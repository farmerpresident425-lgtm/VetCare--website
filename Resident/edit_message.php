<?php
include '../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$newMessage = trim($data['new_message'] ?? '');

if (!$id || $newMessage === '') {
    echo json_encode(["status" => "error", "message" => "Missing ID or message"]);
    exit;
}

$stmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ?");
$stmt->bind_param("si", $newMessage, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Message updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update"]);
}
?>
