<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Absolute path to PHPMailer (inside /phpmailer folder)
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

// Create instance
$mail = new PHPMailer(true);

// Email config
$to = 'annamariesoteromorales@gmail.com'; // ✅ Recipient email
$subject = 'Test Email from VetCare System';
$body = '<h3>This is a test email sent using PHPMailer without Composer!</h3>';

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'annamariesoteromorales@gmail.com'; // ✅ Your Gmail address
    $mail->Password   = 'ssxbsevbbbopxrqg'; // ✅ Gmail App Password (not your normal Gmail password)
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('annamariesoteromorales@gmail.com', 'VetCare System');
    $mail->addAddress($to); // ✅ Send to recipient

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    // Send
    $mail->send();
    echo "✅ Email sent successfully to $to";
} catch (Exception $e) {
    echo "❌ Message could not be sent. Error: {$mail->ErrorInfo}";
}
?>
