<?php
include '../includes/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE messages SET status='read' WHERE id=$id");
}
?>
