<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $to = $_POST['to_email'];
    $subject = $_POST['subject'];
    $message = $_POST['reply_message'];
    $id = (int)$_POST['message_id'];

    $headers = "From: Munikar Lyamha Pucha <munikarlyamhapucha@gmail.com>\r\n";
    $headers .= "Reply-To: munikarlyamhapucha@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8";

    if (mail($to, $subject, $message, $headers)) {
        mysqli_query($conn, "UPDATE messages SET replied='yes' WHERE id=$id");
        header("Location: dashboard.php?replied=1");
    } else {
        header("Location: dashboard.php?error=1");
    }
}
