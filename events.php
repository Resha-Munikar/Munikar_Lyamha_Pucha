<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/db.php';
$result = mysqli_query($conn, "SELECT * FROM events ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Events</title>
<style>
body {
    font-family: 'Open Sans', sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Heading */
h1 {
    text-align: center;
    font-size: 36px;
    color: #b30000;
    margin: 60px 0 30px;
}

/* Event Grid */
.event-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 cards per row */
    gap: 45px;
    max-width: 1200px;
    margin: 0 auto 60px;
    padding: 0 20px;
    align-items: start;
    margin-left: 50px;
}

/* Event Card */
.event-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden;
    height: 420px; /* fixed height */
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.event-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.event-card-content {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    overflow: hidden;
}

/* Card Title */
.event-card h3 {
    margin: 10px 0 8px;
    font-size: 20px;
    color: #b30000;
}

/* Description */
.event-card p {
    font-size: 14px;
    color: #555;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 4; /* limit to 4 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: all 0.3s;
}

.event-card p.expanded {
    display: block;
    overflow: visible;
    -webkit-line-clamp: unset;
    word-break: break-word;
}

/* Read More Button */
.read-more {
    font-size: 14px;
    color: #fff;
    background: #b30000;
    padding: 5px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    align-self: flex-start;
    margin-top: 8px;
    transition: background 0.3s, transform 0.2s;
    text-decoration: none;
    display: inline-block; /* always visible */
}

.read-more:hover {
    background: #ff0000;
    transform: scale(1.05);
}

/* Event Meta */
.event-meta {
    font-size: 14px;
    color: #333;
    margin-top: auto; /* push to bottom */
}

.event-meta strong {
    color: #b30000;
}

/* Responsive adjustments */
@media screen and (max-width: 1200px) {
    .event-container {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media screen and (max-width: 992px) {
    .event-container {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media screen and (max-width: 500px) {
    .event-container {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<h1>Upcoming Events</h1>

<div class="event-container">
<?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="event-card">
        <img src="uploads/gallery/events/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
        <div class="event-card-content">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <span class="read-more">Read More</span>
            <div class="event-meta">
                <strong>Date:</strong> <?= htmlspecialchars($row['event_date']) ?><br>
                <strong>Time:</strong> <?= date('h:i A', strtotime($row['event_time'])) ?>
            </div>

        </div>
    </div>
<?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Toggle Read More / Show Less
document.querySelectorAll('.read-more').forEach(btn => {
    btn.addEventListener('click', () => {
        const p = btn.previousElementSibling;
        p.classList.toggle('expanded');
        btn.innerText = p.classList.contains('expanded') ? 'Show Less' : 'Read More';

        // scroll card into view if expanded
        if (p.classList.contains('expanded')) {
            p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
});
</script>

</body>
</html>
