<?php
require '../config/db.php';
require '../includes/send_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'];
    $schedule = $_POST['schedule'];
    $animal = $_POST['animal'];

    // get email of the resident
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $resident_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    // send the schedule email
    $subject = "Vet Visit Scheduled for Your Animal";
    $body = "A vet visit has been scheduled for your animal <strong>$animal</strong> on <strong>$schedule</strong>. Please be available.";

    if (sendEmail($email, $subject, $body)) {
        echo "Email sent to $email";
    } else {
        echo "Failed to send email.";
    }
}
?>
