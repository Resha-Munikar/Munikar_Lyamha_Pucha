<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    /* -------- SAVE TO DATABASE -------- */
    $sql = "INSERT INTO messages (name, email, subject, message)
            VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {

        /* -------- SEND EMAIL -------- */
        $to = "munikarlyamhapucha@gmail.com";   // your email
        $email_subject = "New Contact Message - Munikar Lyamha Pucha";
        $email_body = "
        Name: $name\n
        Email: $email\n
        Subject: $subject\n\n
        Message:\n$message
        ";

        $headers = "From: $email";

        mail($to, $email_subject, $email_body, $headers);

        /* -------- SUCCESS REDIRECT -------- */
        header("Location: contact.php?success=1");
        exit();

    } else {
        echo "Error: Message not sent.";
    }
}
?>
