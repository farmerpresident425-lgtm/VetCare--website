<?php
include 'send_email.php';
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['notify_type'];
    $subject = $_POST['subject'];
    $body = $_POST['message'];

    if ($type === 'all') {
        $result = $conn->query("SELECT email FROM users WHERE role = 'resident' AND status = 'approved'");
        $successCount = 0;

        while ($row = $result->fetch_assoc()) {
            if (sendEmail($row['email'], $subject, $body)) {
                $successCount++;
            }
        }

        $_SESSION['success'] = "Email sent to $successCount residents.";
    } elseif ($type === 'individual' && !empty($_POST['email'])) {
        $email = $_POST['email'];
        if (sendEmail($email, $subject, $body)) {
            $_SESSION['success'] = "Email sent to $email!";
        } else {
            $_SESSION['error'] = "Failed to send email.";
        }
    } else {
        $_SESSION['error'] = "Please provide valid input.";
    }

    header("Location: vet_notify.php");
    exit;
}
?>
