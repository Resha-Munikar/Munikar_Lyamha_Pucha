<?php
include '../includes/db.php';

// Include PHPMailer manually
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $to = $_POST['to_email'];
    $subject = $_POST['subject'];
    $message = $_POST['reply_message'];
    $id = (int)$_POST['message_id'];

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'munikarlyamhapucha@gmail.com'; // your Gmail
        $mail->Password = 'YOUR_APP_PASSWORD';           // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('munikarlyamhapucha@gmail.com', 'Munikar Lyamha Pucha');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send email
        $mail->send();

        // Update message as replied
        mysqli_query($conn, "UPDATE messages SET replied='yes' WHERE id=$id");

        header("Location: dashboard.php?replied=1");
        exit;

    } catch (Exception $e) {
        // If email fails
        header("Location: dashboard.php?error=1");
        exit;
    }
}
?>
