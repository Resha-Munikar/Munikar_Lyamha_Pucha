<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/db.php';
$result = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Events</title>
<style>
h1{
    text-align:center;
    font-size:36px;
    color:#b30000;
    margin:60px 0 30px;
}
.event-container {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}
.event-card {
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 5px 10px rgba(0,0,0,0.1);
}
.event-card img {
    width:100%;
    height:200px;
    object-fit:cover;
}
</style>
</head>
<body>

<h1>Upcoming Events</h1>

<div class="event-container">

<?php while($row = mysqli_fetch_assoc($result)): ?>
<div class="event-card">
    <img src="uploads/gallery/events/<?= $row['image'] ?>">
    <h3><?= $row['title'] ?></h3>
    <p><?= $row['description'] ?></p>
    <strong>Date:</strong> <?= $row['event_date'] ?><br>
    <strong>Time:</strong> <?= $row['event_time'] ?>
</div>
<?php endwhile; ?>

</div>
</body>
</html>
<?php include 'includes/footer.php'; ?>
