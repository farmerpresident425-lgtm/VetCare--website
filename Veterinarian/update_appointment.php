<?php
session_start();
include '../config/db.php';
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header("Location: ../index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($id && in_array($action, ['approve', 'cancel'])) {
    $status = $action === 'approve' ? 'approved' : 'cancelled';

    // Update appointment
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    if ($action === 'approve') {
        // Get appointment + resident info
        $infoQuery = $conn->prepare("SELECT u.email, u.name, a.appointment_date, a.appointment_time, animal_name AS animal_name
                                     FROM appointments a 
                                     JOIN users u ON a.resident_id = u.id 
                                     JOIN animals an ON a.animal_id = an.id 
                                     WHERE a.id = ?");
        $infoQuery->bind_param("i", $id);
        $infoQuery->execute();
        $result = $infoQuery->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            sendApprovalEmail($data['email'], $data['name'], $data['animal_name'], $data['appointment_date'], $data['appointment_time']);
        }
    }

    header("Location: main.php?section=appointment_request&updated=1");

    exit;
}

function sendApprovalEmail($to, $name, $animal, $date, $time) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'annamariesoteromorales@gmail.com'; // Replace with your Gmail
        $mail->Password   = 'ssxbsevbbbopxrqg'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('annamariesoteromorales@gmail.com', 'VetCare System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Appointment Approved for $animal";
        $mail->Body    = "
            <h3>Hello $name,</h3>
            <p>Your appointment for <strong>$animal</strong> on <strong>$date</strong> at <strong>$time</strong> has been <span style='color:green;'>approved</span>.</p>
            <p>Thank you for using VetCare+!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
    }
}
