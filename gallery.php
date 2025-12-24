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
<title>Gallery</title>

<style>
body {
    font-family: 'Open Sans', sans-serif;
    background: #f3f3f3;
    margin: 0;
}

#mainContent {
    padding: 0 100px 60px;
}

h1 {
    text-align: center;
    font-size: 36px;
    color: #b30000;
    margin:60px 0px 20px 0px;
}

/* Folder grid */
.folders {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 30px;
}

@media (max-width: 1200px) {
    .folders { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 900px) {
    .folders { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 600px) {
    .folders { grid-template-columns: repeat(2, 1fr); }
}

.folder {
    background: #f3f3f3;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    /* box-shadow: 0 6px 18px rgba(0,0,0,0.1); */
}

.folder:hover {
    transform: translateY(-3px);
}

.folder-icon { font-size: 60px; }
.folder-name { font-weight: 600; margin-top: 10px; }

/* OVERLAY */
.folder-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    padding: 40px;
}

.overlay-content {
    max-width: 1200px;
    width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.overlay-content img {
    width: 100%;
    border-radius: 12px;
    cursor: pointer;
}

.close-overlay {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 36px;
    color: white;
    cursor: pointer;
}

/* LIGHTBOX */
.lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    z-index: 20000;
    justify-content: center;
    align-items: center;
}

.lightbox img {
    max-width: 90%;
    max-height: 80%;
    border-radius: 12px;
}

.lightbox .close {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 32px;
    color: white;
    cursor: pointer;
}

/* Navigation arrows */
.nav {
    position: absolute;
    top: 50%;
    font-size: 50px;
    color: white;
    cursor: pointer;
    padding: 12px;
    transform: translateY(-50%);
    user-select: none;
}

.nav.prev { left: 30px; }
.nav.next { right: 30px; }
.nav:hover { color: #ffcc00; }

/* Counter */
.counter {
    position: absolute;
    bottom: 30px;
    color: white;
    font-size: 14px;
    font-weight: 600;
}

.folder-icon i {
    font-size: 50px;      
    color: #A67C37;       
    transition: transform 0.2s;
}

.folder:hover .folder-icon i {
    transform: scale(1.1);  /* Slight zoom on hover */
}

</style>
</head>

<body>

<div id="mainContent">
<h1>Photo Gallery</h1>

<div class="folders">
<?php while($eventRow = mysqli_fetch_assoc($eventsResult)):
    $eventName = $eventRow['event_name'];
    $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $eventName);
?>
    <div class="folder" data-event="<?= $safeFolder ?>">
        <!-- <div class="folder-icon">🖼️</div>   Image folder -->
        <div class="folder-icon"><i class="fa-solid fa-images"></i></div>

        <div class="folder-name"><?= htmlspecialchars($eventName) ?></div>
    </div>
<?php endwhile; ?>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- ================= FOLDER OVERLAY ================= -->
<div class="folder-overlay" id="folderOverlay">
    <span class="close-overlay">&times;</span>
    <div class="overlay-content" id="overlayImages"></div>
</div>

<!-- ================= LIGHTBOX ================= -->
<div class="lightbox" id="lightbox">
    <span class="close" onclick="closeLightbox()">&times;</span>
    <span class="nav prev" onclick="prevImage()">&#10094;</span>
    <img id="lightbox-img" src="" alt="">
    <span class="nav next" onclick="nextImage()">&#10095;</span>
    <div class="counter" id="imageCounter"></div>
</div>

<script>
const imagesGrouped = <?php
$grouped = [];
$all = mysqli_query($conn, "SELECT * FROM gallery ORDER BY uploaded_at DESC");
while($img = mysqli_fetch_assoc($all)) {
    $key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $img['event_name']);
    $grouped[$key][] = $img;
}
echo json_encode($grouped);
?>;

const folders = document.querySelectorAll('.folder');
const overlay = document.getElementById('folderOverlay');
const overlayImages = document.getElementById('overlayImages');
const mainContent = document.getElementById('mainContent');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const counter = document.getElementById('imageCounter');

let currentImages = [];
let currentIndex = 0;

/* Folder open */
folders.forEach(folder => {
    folder.addEventListener('click', () => {
        const event = folder.dataset.event;
        overlayImages.innerHTML = '';
        currentImages = imagesGrouped[event] || [];

        currentImages.forEach((img, index) => {
            const image = document.createElement('img');
            image.src = `uploads/gallery/${event}/${img.filename}`;
            image.onclick = () => {
                currentIndex = index;
                openLightbox();
            };
            overlayImages.appendChild(image);
        });

        mainContent.style.display = 'none';
        overlay.style.display = 'flex';
    });
});

document.querySelector('.close-overlay').onclick = () => {
    overlay.style.display = 'none';
    mainContent.style.display = 'block';
};

/* Lightbox functions */
function openLightbox() {
    lightbox.style.display = 'flex';
    updateLightbox();
}

function updateLightbox() {
    const img = currentImages[currentIndex];
    const folder = img.event_name.replace(/[^a-zA-Z0-9_-]/g,'_');
    lightboxImg.src = `uploads/gallery/${folder}/${img.filename}`;
    counter.innerText = `${currentIndex + 1} / ${currentImages.length}`;
}

function prevImage() {
    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
    updateLightbox();
}

function nextImage() {
    currentIndex = (currentIndex + 1) % currentImages.length;
    updateLightbox();
}

function closeLightbox() {
    lightbox.style.display = 'none';
}

/* Keyboard navigation */
document.addEventListener('keydown', e => {
    if (lightbox.style.display !== 'flex') return;
    if (e.key === 'ArrowLeft') prevImage();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'Escape') closeLightbox();
});

/* Swipe support */
let startX = 0;
lightbox.addEventListener('touchstart', e => startX = e.touches[0].clientX);
lightbox.addEventListener('touchend', e => {
    let endX = e.changedTouches[0].clientX;
    if (startX - endX > 50) nextImage();
    if (endX - startX > 50) prevImage();
});
</script>

</body>
</html>
