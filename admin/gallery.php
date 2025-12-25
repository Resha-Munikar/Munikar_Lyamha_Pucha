<?php
include 'navbar.php';
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle upload
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {

    $event_name = trim($_POST['event_name'] ?? '');
    $files = $_FILES['images'];

    // ✅ LIMIT: max 20 images
    if (count($files['name']) > 20) {
        $error = "You can upload a maximum of 20 images at a time.";
    }
    elseif (!$event_name || empty($files['name'][0])) {
        $error = "Please enter an event name and select at least one image.";
    }
    else {

        $event_folder = '../uploads/gallery/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_name);

        if (!is_dir($event_folder)) {
            mkdir($event_folder, 0777, true);
        }

        $uploaded = 0;
        $failed = 0;

        foreach ($files['name'] as $key => $name) {

            $tmp_name  = $files['tmp_name'][$key];
            $error_code = $files['error'][$key];

            if ($error_code === 0) {

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed)) {
                    $failed++;
                    continue;
                }

                $filename = uniqid() . '.' . $ext;
                $target = $event_folder . '/' . $filename;

                if (move_uploaded_file($tmp_name, $target)) {

                    mysqli_query(
                        $conn,
                        "INSERT INTO gallery (title, filename, event_name)
                         VALUES ('$event_name', '$filename', '$event_name')"
                    );

                    $uploaded++;
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        if ($uploaded > 0) {
            $success = "$uploaded image(s) uploaded successfully!";
        }

        if ($failed > 0) {
            $error = "$failed image(s) failed to upload.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT filename, event_name FROM gallery WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    if ($row) {
        $file_path = '../uploads/gallery/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['event_name']) . '/' . $row['filename'];
        if (file_exists($file_path)) unlink($file_path);
        mysqli_query($conn, "DELETE FROM gallery WHERE id=$id");
        $success = "Image deleted successfully!";
    }
}

// Bulk delete images
if (isset($_GET['bulk_delete'])) {
    $ids = explode(',', $_GET['bulk_delete']);

    foreach ($ids as $id) {
        $id = intval($id);
        $res = mysqli_query($conn, "SELECT filename, event_name FROM gallery WHERE id=$id");
        if ($row = mysqli_fetch_assoc($res)) {
            $path = '../uploads/gallery/' .
                preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['event_name']) .
                '/' . $row['filename'];

            if (file_exists($path)) unlink($path);
            mysqli_query($conn, "DELETE FROM gallery WHERE id=$id");
        }
    }

    $success = count($ids) . " image(s) deleted successfully!";
}

// Delete album
if (isset($_GET['delete_album'])) {
    $event = $_GET['delete_album'];
    $safe_event = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event);
    $folder = "../uploads/gallery/$safe_event";

    if (is_dir($folder)) {
        foreach (glob("$folder/*") as $file) {
            unlink($file);
        }
        rmdir($folder);
    }

    mysqli_query($conn, "DELETE FROM gallery WHERE event_name='$event'");
    $success = "Album deleted successfully!";
}

// Rename album
if (isset($_POST['old_event'], $_POST['new_event'])) {
    $old = $_POST['old_event'];
    $new = $_POST['new_event'];

    $old_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $old);
    $new_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $new);

    $old_path = "../uploads/gallery/$old_safe";
    $new_path = "../uploads/gallery/$new_safe";

    if (is_dir($old_path)) {
        rename($old_path, $new_path);
        mysqli_query($conn, "UPDATE gallery SET event_name='$new' WHERE event_name='$old'");
        $success = "Album renamed successfully!";
    }
}

// Pagination for albums
$albumsPerPage = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $albumsPerPage;

// Fetch albums with LIMIT for pagination
$eventsResult = mysqli_query(
    $conn,
    "SELECT event_name, filename 
     FROM gallery 
     GROUP BY event_name 
     ORDER BY event_name ASC 
     LIMIT $albumsPerPage OFFSET $offset"
);

// Total albums
$totalAlbumsRes = mysqli_query($conn, "SELECT COUNT(DISTINCT event_name) AS total FROM gallery");
$totalAlbumsRow = mysqli_fetch_assoc($totalAlbumsRes);
$totalAlbums = $totalAlbumsRow['total'];
$totalPages = ceil($totalAlbums / $albumsPerPage);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Gallery Management</title>
    <style>
        body { font-family:'Segoe UI', sans-serif; background:#f3f3f3; margin:0; padding:0; }
        .container { max-width:1200px; margin:40px auto; background:white; padding:25px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.08);}

        #uploadBtn { padding:10px 20px; background:#C00000; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-bottom:20px;}
        #uploadBtn:hover { background:#006626; }

        h2 { color:#C00000; margin-bottom:20px; }

        .toast { position: fixed; top: 100px; left: 50%; transform: translateX(-50%); padding: 14px 20px; border-radius: 8px; color: white; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; z-index: 10000; animation: slideIn 0.4s ease;}
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
        @keyframes slideIn { from { opacity: 0; transform: translate(-50%, -20px); } to { opacity: 1; transform: translate(-50%, 0); } }

        .folders { display:flex; flex-wrap:wrap; gap:20px; }
        .folder { display:flex; flex-direction:column; align-items:center; cursor:pointer; width:180px; }
        .folder img { width:150px; height:150px; object-fit:cover; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .folder img:hover { transform: scale(1.05); }
        .folder-name { margin-top:8px; font-weight:600; text-align:center; word-wrap:break-word; }

        .folder-overlay {position: fixed; inset: 0; background: rgba(0,0,0,0.85); display: none; justify-content: center; align-items: center; z-index: 2000; overflow: auto; padding: 40px; flex-direction: column;}
        .folder-overlay .overlay-content { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; max-width: 1200px; width: 100%; }
        .folder-overlay .image-container { position: relative; }
        .folder-overlay img { width:100%; border-radius:8px; cursor:pointer; transition: none; }
        .folder-overlay .delete-btn { position:absolute; top:5px; right:5px; background:red; color:white; border:none; border-radius:4px; padding:2px 6px; cursor:pointer; font-size:12px; }
        .folder-overlay .close-overlay { position:absolute; top:20px; right:30px; font-size:36px; color:white; cursor:pointer; font-weight:bold; }

        .overlay-counter { color:white; font-weight:600; margin-top:10px; }

        .lightbox { display:none; position:fixed; z-index:3000; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.9); justify-content:center; align-items:center; }
        .lightbox img { max-width:90%; max-height:90%; border-radius:6px; }
        .close-lightbox { position:absolute; top:20px; right:30px; font-size:36px; color:white; cursor:pointer; }
        .prev, .next { position:absolute; top:50%; font-size:48px; color:white; cursor:pointer; padding:10px; transform: translateY(-50%);}
        .prev { left:30px; } .next { right:30px; }
        .prev:hover, .next:hover { color:#ffcc00; }

        .upload-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); justify-content:center; align-items:center; z-index:4000; padding:20px; }
        .upload-modal .modal-content { background: white; padding: 24px 28px; border-radius: 12px; max-width: 480px; width: 100%; position: relative; box-shadow: 0 12px 35px rgba(0,0,0,0.25); }
        .upload-modal .close-upload { position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; font-weight:bold; color:#333; }
        .upload-modal input[type="text"], .upload-modal input[type="file"] { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
        .upload-modal label { font-weight: 600; margin-bottom: 6px; display: block; }
        .upload-modal button { padding:10px 20px; background:#008736; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        .upload-modal button:hover { background:#006626; }
        .rename-btn, .delete-btn { background: #eaeaea; border: none; padding: 5px 8px; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .rename-btn:hover { background: #ffd966; }
        .delete-btn:hover { background: #ff4d4d; color: white; }
        .image-checkbox { position: absolute; top: 8px; left: 8px; z-index: 10; width: 18px; height: 18px; cursor: pointer; }

        .bulk-actions { grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .bulk-actions label { color: white; font-weight: 600; }
        .bulk-actions button { background: #dc3545; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .bulk-actions button:hover { background: #b52a37; }
        .overlay-counter { display: none;}
        .lightbox-counter { position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%); color: #ffffff; font-size: 14px; font-weight: 600; opacity: 0.9; }

    </style>
</head>
<body>
<?php if ($success): ?>
<div class="toast success">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="toast error">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="container">

    <button id="uploadBtn"><span style="font-size:18px; font-weight:700;"> + </span> Upload Images</button>

    <h2>📂 Albums</h2>
    <div class="folders">
        <?php while($event = mysqli_fetch_assoc($eventsResult)):
            $event_folder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['event_name']);
            $folder_thumb = $event['filename'] ? "../uploads/gallery/$event_folder/" . $event['filename'] : "../images/folder.png";
        ?>
        <div class="folder" data-event="<?= $event_folder ?>" data-name="<?= htmlspecialchars($event['event_name']) ?>">
            <img src="<?= $folder_thumb ?>" alt="folder icon">
            <div class="folder-name"><?= htmlspecialchars($event['event_name']) ?></div>
            <div style="margin-top:6px; display:flex; gap:6px;">
                <button class="rename-btn">✏️</button>
                <button class="delete-btn">🗑️</button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <div style="margin-top:30px; text-align:center;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>" style="margin:0 8px; text-decoration:none; font-weight:600;">⬅ Prev</a>
        <?php endif; ?>
        <?php for ($i=1; $i<=$totalPages; $i++): ?>
            <a href="?page=<?= $i ?>"
               style="margin:0 6px; padding:6px 10px; border-radius:6px;
               <?= $i==$page ? 'background:#008736;color:white;' : 'background:#eaeaea;color:#333;' ?>
               text-decoration:none; font-weight:600;">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>" style="margin:0 8px; text-decoration:none; font-weight:600;">Next ➡</a>
        <?php endif; ?>
    </div>

</div>

<!-- Upload Modal -->
<div class="upload-modal" id="uploadModal">
    <div class="modal-content">
        <span class="close-upload">&times;</span>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Event Name</label>
            <input type="text" name="event_name" id="eventNameInput" placeholder="Enter event name" required>
            <label>Select Images</label>
            <input type="file" name="images[]" multiple required>
            <div style="text-align:right; margin-top:10px;">
                <button type="submit">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Folder Overlay -->
<div class="folder-overlay" id="folderOverlay">
    <span class="close-overlay">&times;</span>

    <!-- Upload more button -->
    <div style="margin-bottom:20px;">
        <button id="uploadMoreBtn"
            style="padding:10px 18px;background:#008736;color:#fff;border:none;
            border-radius:6px;font-weight:600;cursor:pointer;">
            ➕ Upload More Photos
        </button>
    </div>

    <div class="overlay-content" id="overlayImages"></div>
    <div class="overlay-counter" id="overlayCounter"></div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
    <span class="close-lightbox">&times;</span>
    <img id="lightboxImg" src="" alt="">
    <span class="prev">&#10094;</span>
    <span class="next">&#10095;</span>
    <div class="lightbox-counter" id="lightboxCounter"></div>
</div>

<div class="upload-modal" id="renameModal">
    <div class="modal-content">
        <span class="close-upload" onclick="renameModal.style.display='none'">&times;</span>
        <form method="POST">
            <input type="hidden" name="old_event" id="oldEvent">
            <label>New Album Name</label>
            <input type="text" name="new_event" required>
            <button type="submit">Rename</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const fileInput = document.querySelector('input[name="images[]"]');

    if (fileInput) {
        fileInput.addEventListener('change', function () {

            if (this.files.length > 20) {
                alert("⚠️ Maximum 20 images allowed per upload.");
                this.value = ""; // clear selection
            }

        });
    }
});
const IMAGES_PER_PAGE = 12;
let overlayPage = 1;

setTimeout(() => document.querySelectorAll('.toast').forEach(t => t.remove()), 3500);

const uploadBtn = document.getElementById('uploadBtn');
const uploadModal = document.getElementById('uploadModal');
const closeUpload = document.querySelector('.close-upload');
uploadBtn.addEventListener('click', () => {
    eventNameInput.value = '';
    eventNameInput.readOnly = false;
    uploadModal.style.display = 'flex';
});
closeUpload.addEventListener('click', () => uploadModal.style.display = 'none');
window.addEventListener('click', e => { if(e.target==uploadModal) uploadModal.style.display='none'; });

const imagesGrouped = <?php
$imagesGrouped = [];
$allImages = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC");
while($img = mysqli_fetch_assoc($allImages)){
    $event_folder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $img['event_name']);
    $imagesGrouped[$event_folder][] = $img;
}
echo json_encode($imagesGrouped);
?>;

const fileInput = document.querySelector('input[name="images[]"]');

fileInput.addEventListener('change', function () {
    if (this.files.length > 20) {
        alert("⚠️ You can upload a maximum of 20 images at a time.");
        this.value = ""; // reset file selection
    }
});

const folderOverlay = document.getElementById('folderOverlay');
const overlayImages = document.getElementById('overlayImages');
const closeOverlay = document.querySelector('.close-overlay');
const overlayCounter = document.getElementById('overlayCounter');

const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
const closeLightbox = document.querySelector('.close-lightbox');
const prevBtn = document.querySelector('.prev');
const nextBtn = document.querySelector('.next');

let currentImages = [];
let currentIndex = 0;
let activeEvent = '';

document.querySelectorAll('.folder').forEach(folder => {
    folder.addEventListener('click', () => {
        activeEvent = folder.getAttribute('data-event');
        currentImages = imagesGrouped[activeEvent] || [];
        overlayPage = 1;
        renderOverlayImages(activeEvent);
        folderOverlay.style.display = 'flex';
    });

    folder.querySelector('.rename-btn').onclick = e => {
        e.stopPropagation();
        document.getElementById('oldEvent').value = folder.dataset.name;
        document.getElementById('renameModal').style.display='flex';
    };

    folder.querySelector('.delete-btn').onclick = e => {
        e.stopPropagation();
        if(confirm('Delete entire album?')) window.location.href=`?delete_album=${folder.dataset.name}`;
    };
});

closeOverlay.onclick = () => folderOverlay.style.display='none';
window.onclick = e => { if(e.target==folderOverlay) folderOverlay.style.display='none'; };
closeLightbox.onclick = () => lightbox.style.display='none';
window.addEventListener('keydown', e => {
    if(lightbox.style.display==='flex'){
        if(e.key==='ArrowLeft'){ currentIndex = (currentIndex-1+currentImages.length)%currentImages.length; showLightboxImage(currentIndex);}
        if(e.key==='ArrowRight'){ currentIndex = (currentIndex+1)%currentImages.length; showLightboxImage(currentIndex);}
    }
});

prevBtn.onclick = () => { currentIndex = (currentIndex-1+currentImages.length)%currentImages.length; showLightboxImage(currentIndex);}
nextBtn.onclick = () => { currentIndex = (currentIndex+1)%currentImages.length; showLightboxImage(currentIndex);}

function showLightboxImage(index){
    if(currentImages[index]){
        lightboxImg.src = `../uploads/gallery/${currentImages[index].event_name.replace(/[^a-zA-Z0-9_-]/g,'_')}/${currentImages[index].filename}`;
        currentIndex = index;

        document.getElementById('lightboxCounter').innerText =
            `${currentIndex + 1} / ${currentImages.length}`;
    }
}

function renderOverlayImages(event){
    overlayImages.innerHTML='';
    const bulkBar = document.createElement('div');
    bulkBar.className='bulk-actions';
    bulkBar.innerHTML = `<label><input type="checkbox" id="selectAll"> Select All</label>
                         <button id="deleteSelected">🗑 Delete Selected</button>`;
    overlayImages.appendChild(bulkBar);

    const start = (overlayPage-1)*IMAGES_PER_PAGE;
    const end = start+IMAGES_PER_PAGE;
    const pageImages = currentImages.slice(start,end);

    pageImages.forEach((img,i)=>{
        const container = document.createElement('div'); container.className='image-container';
        const checkbox = document.createElement('input'); checkbox.type='checkbox'; checkbox.className='image-checkbox'; checkbox.value=img.id;
        const imgEl = document.createElement('img'); imgEl.src=`../uploads/gallery/${event}/${img.filename}`; imgEl.alt=img.title;
    imgEl.onclick = () => {
        currentIndex = start + i;
        showLightboxImage(currentIndex);
        lightbox.style.display = 'flex';
    };
        container.appendChild(checkbox); container.appendChild(imgEl); overlayImages.appendChild(container);
    });

    document.getElementById('selectAll').onclick=function(){document.querySelectorAll('.image-checkbox').forEach(cb=>cb.checked=this.checked);}
    document.getElementById('deleteSelected').onclick=function(){
        const selected=[...document.querySelectorAll('.image-checkbox:checked')].map(cb=>cb.value);
        if(!selected.length) return alert('No images selected');
        if(!confirm(`Delete ${selected.length} selected image(s)?`)) return;
        window.location.href=`?bulk_delete=${selected.join(',')}`;
    }

    const totalPages = Math.ceil(currentImages.length/IMAGES_PER_PAGE);
    if(totalPages<=1){ overlayCounter.innerText=`${start+1} - ${Math.min(end,currentImages.length)} / ${currentImages.length}`; return;}
    overlayCounter.innerText=`${start+1} - ${Math.min(end,currentImages.length)} / ${currentImages.length}`;

    const nav = document.createElement('div'); nav.style.cssText='grid-column:1/-1; display:flex; justify-content:center; gap:12px; margin-top:20px;';
    if(overlayPage>1){ const prev=document.createElement('button'); prev.innerText='⬅ Prev'; prev.onclick=()=>{overlayPage--; renderOverlayImages(event);}; nav.appendChild(prev);}
    const pageInfo=document.createElement('span'); pageInfo.style.color='#fff'; pageInfo.style.fontWeight='600'; pageInfo.innerText=`Page ${overlayPage} / ${totalPages}`; nav.appendChild(pageInfo);
    if(overlayPage<totalPages){ const next=document.createElement('button'); next.innerText='Next ➡'; next.onclick=()=>{overlayPage++; renderOverlayImages(event);}; nav.appendChild(next);}
    overlayImages.appendChild(nav);
}

const uploadMoreBtn = document.getElementById('uploadMoreBtn');
const eventNameInput = document.getElementById('eventNameInput');

uploadMoreBtn.addEventListener('click', () => {

    // Get readable event name from current folder
    const originalEventName = currentImages.length
        ? currentImages[0].event_name
        : '';

    // Set event name & lock it
    eventNameInput.value = originalEventName;
    eventNameInput.readOnly = true;

    uploadModal.style.display = 'flex';
});
</script>
</body>
</html>
