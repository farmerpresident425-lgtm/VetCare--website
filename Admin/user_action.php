<?php
require '../config/db.php';
header('Content-Type: application/json');

// 🔐 Check if POST method with required fields
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    // ✅ Only allow "approve" or "deny"
    if (!in_array($action, ['approve', 'deny'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid action value']);
        exit;
    }

    $status = $action === 'approve' ? 'approved' : 'denied';

    // 🛠️ Execute update
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => "User has been $status."]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No user updated. Possibly already in this state.']);
    }

    $stmt->close();
    exit;
}

// ❌ Fallback error
echo json_encode(['success' => false, 'error' => 'Invalid request format.']);
