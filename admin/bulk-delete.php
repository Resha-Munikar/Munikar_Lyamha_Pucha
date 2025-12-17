<?php
include '../includes/db.php';

if (!empty($_POST['ids'])) {
    $ids = implode(',', array_map('intval', $_POST['ids']));
    mysqli_query($conn, "DELETE FROM messages WHERE id IN ($ids)");
}

header("Location: dashboard.php");
