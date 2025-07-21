<?php
include '../config/db.php';

// Get the JSON payload from the fetch() request
$data = json_decode(file_get_contents("php://input"), true);

// Extract values
$id = $data['id'] ?? null;
$newMessage = trim($data['new_message'] ?? '');

if ($id && $newMessage !== '') {
    // Update the message
    $stmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ?");
    $stmt->bind_param("si", $newMessage, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing ID or message content"]);
}
?>
