<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/db.php';

// Fetch all distinct events
$eventsResult = mysqli_query($conn, "SELECT DISTINCT event_name FROM gallery ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Gallery by Event</title>
<style>
body { font-family: Arial, sans-serif; background: #f3f3f3; margin: 0; padding: 40px;}
h1 { text-align: center; color: #b30000; margin-bottom: 30px; }
h2 { color: #008736; margin-top: 40px; }
.gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.gallery-item { position: relative; overflow: hidden; border-radius: 12px; cursor: pointer; }
.gallery-item img { width: 100%; display: block; border-radius: 12px; }
.overlay { position: absolute; bottom: 0; width: 100%; background: rgba(0,0,0,0.6); color: white; padding: 10px; text-align: center; font-size: 14px; opacity: 0; transition: opacity 0.3s;}
.gallery-item:hover .overlay { opacity: 1; }
.lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 10000; justify-content: center; align-items: center; padding: 20px;}
.lightbox img { max-width: 90%; max-height: 80%; border-radius: 12px; }
.lightbox .close { position: absolute; top: 20px; right: 30px; font-size: 30px; color: white; cursor: pointer; }
</style>
</head>
<body>

<h1>📸 Photo Gallery by Event</h1>

<?php while($eventRow = mysqli_fetch_assoc($eventsResult)): 
    $eventName = $eventRow['event_name'];
    $folder = 'uploads/gallery/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $eventName) . '/';
    $imagesResult = mysqli_query($conn, "SELECT * FROM gallery WHERE event_name='$eventName' ORDER BY uploaded_at DESC");
?>
    <h2><?= htmlspecialchars($eventName) ?></h2>
    <div class="gallery">
        <?php while($img = mysqli_fetch_assoc($imagesResult)): ?>
            <div class="gallery-item">
                <img src="<?= $folder . $img['filename'] ?>" alt="<?= htmlspecialchars($img['title']) ?>">
                <div class="overlay"><?= htmlspecialchars($img['title']) ?></div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endwhile; ?>

<div class="lightbox" id="lightbox">
    <span class="close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="">
</div>

<script>
// Lightbox functionality
const galleryItems = document.querySelectorAll('.gallery-item');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');

galleryItems.forEach(item => {
    item.addEventListener('click', () => {
        lightbox.style.display = 'flex';
        lightboxImg.src = item.querySelector('img').src;
        lightboxImg.alt = item.querySelector('img').alt;
    });
});

function closeLightbox() { lightbox.style.display = 'none'; }
</script>

</body>
</html>
<?php include 'includes/footer.php'; ?>
