<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'norbert200476@gmail.com';
    $mail->Password   = 'gojd eoho wdam ctqa'; // Use App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('norbert200476@gmail.com', 'Gyorgy Norbert');
    $mail->addAddress('norbert200476@gmail.com');
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email from PHPMailer.';
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->send();
    echo 'Test email sent!';
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>