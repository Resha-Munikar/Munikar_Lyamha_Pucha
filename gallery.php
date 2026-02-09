<?php
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/db.php';

/* Fetch events */
$eventsResult = mysqli_query($conn, "SELECT DISTINCT event_name FROM gallery ORDER BY uploaded_at DESC");

/* Group images */
$grouped = [];
$all = mysqli_query($conn, "SELECT event_name, filename FROM gallery ORDER BY uploaded_at DESC");
while($img = mysqli_fetch_assoc($all)) {
    $key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $img['event_name']);
    $grouped[$key][] = $img;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Gallery</title>

<style>
body{
    font-family: 'Open Sans', sans-serif;
    background:#f4f4f4;
    margin:0;
}

#mainContent{
    max-width:1400px;
    margin:auto;
    padding:20px 20px 60px;
}

h1{
    text-align:center;
    font-size:36px;
    color:#b30000;
    margin:60px 0 30px;
}

/* FOLDERS */
.folders{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:30px;
}

.folder{
    background:white;
    border-radius:14px;
    padding:25px;
    text-align:center;
    cursor:pointer;
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
    transition:.3s;
}

.folder:hover{
    transform:translateY(-5px);
}

.folder-icon i{
    font-size:50px;
    color:#A67C37;
}

.folder-name{
    font-weight:600;
    margin-top:10px;
}

.folder small{
    color:#777;
}

/* OVERLAY */
.folder-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.9);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:10000;
    padding:40px;
}

.overlay-box{
    width:100%;
    max-width:1200px;
    background:#111;
    border-radius:12px;
    padding:20px;
}

.overlay-title{
    color:white;
    text-align:center;
    margin-bottom:15px;
    font-size:22px;
}

/* GRID WITH SCROLLBAR */
.overlay-content{
    max-height:75vh;
    overflow-y:auto;

    display:grid;
    grid-template-columns:repeat(auto-fill,220px);
    gap:20px;
    justify-content:center;
    padding-right:10px;
}

/* scrollbar */
.overlay-content::-webkit-scrollbar{
    width:8px;
}
.overlay-content::-webkit-scrollbar-thumb{
    background:#ffcc00;
    border-radius:10px;
}
.overlay-content::-webkit-scrollbar-track{
    background:rgba(255,255,255,0.1);
}

/* IMAGE BOX FIXED SIZE */
.img-box{
    width:220px;
    height:220px;
    overflow:hidden;
    border-radius:12px;
}

.img-box img{
    width:100%;
    height:100%;
    object-fit:cover;
    cursor:pointer;
    transition:transform .3s;
}

.img-box img:hover{
    transform:scale(1.05);
}

.close-overlay{
    position:absolute;
    top:20px;
    right:30px;
    font-size:36px;
    color:white;
    cursor:pointer;
}

/* LIGHTBOX */
.lightbox{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.95);
    z-index:20000;
    justify-content:center;
    align-items:center;
}

.lightbox img{
    max-width:90%;
    max-height:80%;
    border-radius:12px;
}

.lightbox .close{
    position:absolute;
    top:20px;
    right:30px;
    font-size:32px;
    color:white;
    cursor:pointer;
}

.nav{
    position:absolute;
    top:50%;
    font-size:50px;
    color:white;
    cursor:pointer;
    transform:translateY(-50%);
}

.nav.prev{ left:30px; }
.nav.next{ right:30px; }
.nav:hover{ color:#ffcc00; }

.counter{
    position:absolute;
    bottom:30px;
    color:white;
}

.download-btn{
    position:absolute;
    top:20px;
    left:30px;
    background:#ffcc00;
    padding:8px 14px;
    border-radius:6px;
    text-decoration:none;
    color:black;
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
    $count = count($grouped[$safeFolder] ?? []);
?>
<div class="folder" data-event="<?= $safeFolder ?>" data-name="<?= htmlspecialchars($eventName) ?>">
    <div class="folder-icon"><i class="fa-solid fa-images"></i></div>
    <div class="folder-name"><?= htmlspecialchars($eventName) ?></div>
    <small><?= $count ?> Photos</small>
</div>
<?php endwhile; ?>
</div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- OVERLAY -->
<div class="folder-overlay" id="folderOverlay">
    <span class="close-overlay">&times;</span>

    <div class="overlay-box">
        <div class="overlay-title" id="overlayTitle"></div>
        <div class="overlay-content" id="overlayImages"></div>
    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
    <span class="close" onclick="closeLightbox()">&times;</span>
    <a id="downloadBtn" class="download-btn" download>⬇ Download</a>

    <span class="nav prev" onclick="prevImage()">&#10094;</span>
    <img id="lightbox-img">
    <span class="nav next" onclick="nextImage()">&#10095;</span>
    <div class="counter" id="imageCounter"></div>
</div>

<script>
const imagesGrouped = <?= json_encode($grouped); ?>;

const folders = document.querySelectorAll('.folder');
const overlay = document.getElementById('folderOverlay');
const overlayImages = document.getElementById('overlayImages');
const overlayTitle = document.getElementById('overlayTitle');
const mainContent = document.getElementById('mainContent');

const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const counter = document.getElementById('imageCounter');
const downloadBtn = document.getElementById('downloadBtn');

let currentImages = [];
let currentIndex = 0;

/* OPEN FOLDER */
folders.forEach(folder=>{
    folder.addEventListener('click',()=>{
        const event = folder.dataset.event;
        const name = folder.dataset.name;

        overlayTitle.innerText = name;
        overlayImages.innerHTML = '';
        currentImages = imagesGrouped[event] || [];

        currentImages.forEach((img,index)=>{
            const box = document.createElement('div');
            box.className = "img-box";

            const image = document.createElement('img');
            image.src = `uploads/gallery/${event}/${img.filename}`;
            image.onclick = ()=>{
                currentIndex = index;
                openLightbox();
            };

            box.appendChild(image);
            overlayImages.appendChild(box);
        });

        document.body.style.overflow="hidden";
        mainContent.style.display="none";
        overlay.style.display="flex";
    });
});

/* CLOSE OVERLAY */
document.querySelector('.close-overlay').onclick = ()=>{
    overlay.style.display="none";
    mainContent.style.display="block";
    document.body.style.overflow="auto";
};

/* LIGHTBOX */
function openLightbox(){
    lightbox.style.display="flex";
    updateLightbox();
}

function updateLightbox(){
    const img = currentImages[currentIndex];
    const folder = img.event_name.replace(/[^a-zA-Z0-9_-]/g,'_');
    const path = `uploads/gallery/${folder}/${img.filename}`;

    lightboxImg.src = path;
    counter.innerText = `${currentIndex+1} / ${currentImages.length}`;
    downloadBtn.href = path;
}

function prevImage(){
    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
    updateLightbox();
}

function nextImage(){
    currentIndex = (currentIndex + 1) % currentImages.length;
    updateLightbox();
}

function closeLightbox(){
    lightbox.style.display="none";
}

/* KEYBOARD */
document.addEventListener('keydown',e=>{
    if(lightbox.style.display!=="flex") return;
    if(e.key==="ArrowLeft") prevImage();
    if(e.key==="ArrowRight") nextImage();
    if(e.key==="Escape") closeLightbox();
});
</script>

</body>
</html>
