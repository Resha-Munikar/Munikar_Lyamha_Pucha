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
    $event_name = $_POST['event_name'] ?? '';
    $files = $_FILES['images'];

    if (!$event_name || !$files) {
        $error = "Please enter an event name and select at least one image.";
    } else {
        $event_folder = '../uploads/gallery/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_name);
        if (!is_dir($event_folder)) mkdir($event_folder, 0777, true);

        $uploaded = 0;
        $failed = 0;

        foreach ($files['name'] as $key => $name) {
            $tmp_name = $files['tmp_name'][$key];
            $error_code = $files['error'][$key];

            if ($error_code === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid() . "." . $ext;
                $target = $event_folder . '/' . $filename;

                if (move_uploaded_file($tmp_name, $target)) {
                    mysqli_query($conn, "INSERT INTO gallery (title, filename, event_name) VALUES ('$event_name', '$filename', '$event_name')");
                    $uploaded++;
                } else $failed++;
            } else $failed++;
        }

        if ($uploaded > 0) $success = "$uploaded image(s) uploaded successfully!";
        if ($failed > 0) $error = "$failed image(s) failed to upload.";
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

// Delete album
if (isset($_GET['delete_album'])) {
    $event = $_GET['delete_album'];
    $safe_event = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event);
    $folder = "../uploads/gallery/$safe_event";

    // Delete images
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

// Fetch albums
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

        /* Upload button */
        #uploadBtn { padding:10px 20px; background:#008736; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-bottom:20px;}
        #uploadBtn:hover { background:#006626; }

        h2 { color:#C00000; margin-bottom:20px; }

        /* Toast Notifications */
        .toast { position: fixed; top: 100px; left: 50%; transform: translateX(-50%); padding: 14px 20px; border-radius: 8px; color: white; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; z-index: 10000; animation: slideIn 0.4s ease;}
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
        @keyframes slideIn { from { opacity: 0; transform: translate(-50%, -20px); } to { opacity: 1; transform: translate(-50%, 0); } }

        /* Folder view */
        .folders { display:flex; flex-wrap:wrap; gap:20px; }
        .folder { display:flex; flex-direction:column; align-items:center; cursor:pointer; width:180px; }
        .folder img { width:150px; height:150px; object-fit:cover; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .folder img:hover { transform: scale(1.05); }
        .folder-name { margin-top:8px; font-weight:600; text-align:center; word-wrap:break-word; }

        /* Overlay for folder images */
        .folder-overlay { position: fixed; top:0; left:0; width:99%; height:90%; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); display: none; justify-content: center; align-items: center; z-index: 2000; overflow: auto; padding: 40px 20px; }
        .folder-overlay .overlay-content { display: grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap: 20px; max-width: 1200px; width: 100%; }
        .folder-overlay .image-container { position: relative; }
        .folder-overlay img { width:100%; border-radius:8px; cursor:pointer; transition: transform 0.3s; }
        .folder-overlay img:hover { transform: scale(1.05); }
        .folder-overlay .delete-btn { position:absolute; top:5px; right:5px; background:red; color:white; border:none; border-radius:4px; padding:2px 6px; cursor:pointer; font-size:12px; }
        .folder-overlay .close-overlay { position:absolute; top:20px; right:30px; font-size:36px; color:white; cursor:pointer; font-weight:bold; }

        /* Lightbox */
        .lightbox { display:none; position:fixed; z-index:3000; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.9); justify-content:center; align-items:center; }
        .lightbox img { max-width:90%; max-height:90%; border-radius:6px; }
        .close-lightbox { position:absolute; top:20px; right:30px; font-size:36px; color:white; cursor:pointer; }
        .prev, .next { position:absolute; top:50%; font-size:48px; color:white; cursor:pointer; padding:10px; transform: translateY(-50%);}
        .prev { left:30px; } .next { right:30px; }
        .prev:hover, .next:hover { color:#ffcc00; }

        /* Upload Modal */
        .upload-modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); justify-content:center; align-items:center; z-index:4000; padding:20px; }
        .upload-modal .modal-content {
            background: white;
            padding: 24px 28px;      /* better inner spacing */
            border-radius: 12px;
            max-width: 480px;        /* smaller modal width */
            width: 100%;
            position: relative;
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
        }
        .upload-modal .close-upload { position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; font-weight:bold; color:#333; }
        .upload-modal input[type="text"], .upload-modal input[type="file"] { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
        .upload-modal label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .upload-modal input[type="text"],
        .upload-modal input[type="file"] {
            margin-bottom: 12px;
        }

        .upload-modal button { padding:10px 20px; background:#008736; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        .upload-modal button:hover { background:#006626; }
        .rename-btn, .delete-btn {
            background: #eaeaea;
            border: none;
            padding: 5px 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
        }

        .rename-btn:hover { background: #ffd966; }
        .delete-btn:hover { background: #ff4d4d; color: white; }

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

    <!-- Upload button -->
    <button id="uploadBtn">📤 Upload Images</button>

    <h2>📂 Albums</h2>
    <div class="folders">
        <?php
        $eventsResult = mysqli_query($conn, "SELECT event_name, filename FROM gallery GROUP BY event_name ORDER BY event_name ASC");
        while($event = mysqli_fetch_assoc($eventsResult)):
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

    <?php
    $imagesGrouped = [];
    $allImages = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC");
    while($img = mysqli_fetch_assoc($allImages)){
        $event_folder = preg_replace('/[^a-zA-Z0-9_-]/', '_', $img['event_name']);
        $imagesGrouped[$event_folder][] = $img;
    }
    ?>

</div>

<!-- Upload Modal -->
<div class="upload-modal" id="uploadModal">
    <div class="modal-content">
        <span class="close-upload">&times;</span>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Event Name</label>
            <input type="text" name="event_name" placeholder="Enter event name" required>
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
    <div class="overlay-content" id="overlayImages"></div>
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

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
    <span class="close-lightbox">&times;</span>
    <img id="lightboxImg" src="" alt="">
    <span class="prev">&#10094;</span>
    <span class="next">&#10095;</span>
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
    // Auto hide toast
    setTimeout(() => {
        document.querySelectorAll('.toast').forEach(t => t.remove());
    }, 3500);

    // Upload Modal
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadModal = document.getElementById('uploadModal');
    const closeUpload = document.querySelector('.close-upload');
    uploadBtn.addEventListener('click', () => { uploadModal.style.display = 'flex'; });
    closeUpload.addEventListener('click', () => { uploadModal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if(e.target==uploadModal) uploadModal.style.display='none'; });

    const imagesGrouped = <?php echo json_encode($imagesGrouped); ?>;
    const folderOverlay = document.getElementById('folderOverlay');
    const overlayImages = document.getElementById('overlayImages');
    const closeOverlay = document.querySelector('.close-overlay');

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const closeLightbox = document.querySelector('.close-lightbox');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');

    let currentImages = [];
    let currentIndex = 0;

    // Folder Overlay
    document.querySelectorAll('.folder').forEach(folder => {
        folder.addEventListener('click', () => {
            const event = folder.getAttribute('data-event');
            currentImages = imagesGrouped[event] || [];
            overlayImages.innerHTML = '';

            currentImages.forEach((img, index) => {
                const container = document.createElement('div');
                container.className = 'image-container';

                const imgEl = document.createElement('img');
                imgEl.src = `../uploads/gallery/${event}/${img.filename}`;
                imgEl.alt = img.title;

                imgEl.onclick = () => {
                    currentIndex = index;
                    lightboxImg.src = imgEl.src;
                    lightbox.style.display = 'flex';
                }

                const delBtn = document.createElement('button');
                delBtn.className = 'delete-btn';
                delBtn.innerText = '✖';
                delBtn.onclick = function(e){
                    e.stopPropagation();
                    if(confirm('Delete this image?')){
                        window.location.href = `?delete=${img.id}`;
                    }
                };

                container.appendChild(imgEl);
                container.appendChild(delBtn);
                overlayImages.appendChild(container);
            });

            folderOverlay.style.display = 'flex';
        });
    });

    closeOverlay.onclick = () => folderOverlay.style.display = 'none';
    window.onclick = (e) => { if(e.target==folderOverlay) folderOverlay.style.display='none'; };

    // Lightbox
    closeLightbox.onclick = () => lightbox.style.display='none';
    window.onclick = (e) => { if(e.target==lightbox) lightbox.style.display='none'; };

    function showLightboxImage(index){
        if(currentImages[index]){
            lightboxImg.src = `../uploads/gallery/${currentImages[index].event_name.replace(/[^a-zA-Z0-9_-]/g,'_')}/${currentImages[index].filename}`;
            currentIndex = index;
        }
    }
    prevBtn.onclick = () => { currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length; showLightboxImage(currentIndex); };
    nextBtn.onclick = () => { currentIndex = (currentIndex + 1) % currentImages.length; showLightboxImage(currentIndex); };
    const renameModal = document.getElementById('renameModal');
    const oldEventInput = document.getElementById('oldEvent');

    document.querySelectorAll('.folder').forEach(folder => {

        // Prevent album open when clicking buttons
        folder.querySelector('.rename-btn').onclick = (e) => {
            e.stopPropagation();
            oldEventInput.value = folder.dataset.name;
            renameModal.style.display = 'flex';
        };

        folder.querySelector('.delete-btn').onclick = (e) => {
            e.stopPropagation();
            if (confirm('Delete entire album?')) {
                window.location.href = `?delete_album=${folder.dataset.name}`;
            }
        };

    });

</script>
</body>
</html>
