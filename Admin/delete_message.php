<?php
include '../config/db.php';

// Get the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Extract message ID
$id = $data['id'] ?? null;

if ($id) {
    // Delete the message
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "deleted"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Message ID is required"]);
}
?>
