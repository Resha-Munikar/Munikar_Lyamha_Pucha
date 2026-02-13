<?php 
include_once 'navbar.php'; 
include_once '../includes/db.php'; 
include_once 'functions.php';

$msg = "";

/* ---------------- BULK DELETE ---------------- */
if (isset($_POST['bulk_delete'])) {
    $ids = $_POST['event_ids'] ?? [];
    if(!empty($ids)){
        foreach($ids as $id){
            deleteEvent($id, $conn);
        }
        header("Location: manage-events.php?success=deleted");
        exit();
    }
}

/* ---------------- ADD EVENT ---------------- */
if (isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'] ?? null;

    $imageName = "";
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $imageName = uniqid()."_".time().".".$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/gallery/events/".$imageName);
        }
    }

    $stmt = $conn->prepare("INSERT INTO events(title,description,event_date,event_time,image) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssss", $title, $description, $event_date, $event_time, $imageName);
    $stmt->execute();

    header("Location: manage-events.php?success=added");
    exit();
}

/* ---------------- DELETE EVENT ---------------- */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    deleteEvent($id, $conn);
    header("Location: manage-events.php?success=deleted");
    exit();
}

/* ---------------- FETCH EDIT DATA ---------------- */
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

/* ---------------- UPDATE EVENT ---------------- */
if (isset($_POST['update_event'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'] ?? null;

    $imageName = $_POST['old_image'];

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $imageName = uniqid()."_".time().".".$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/gallery/events/".$imageName);
            if($_POST['old_image'] && file_exists("../uploads/gallery/events/".$_POST['old_image'])){
                unlink("../uploads/gallery/events/".$_POST['old_image']);
            }
        }
    }

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, image=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $description, $event_date, $event_time, $imageName, $id);
    $stmt->execute();

    header("Location: manage-events.php?success=updated");
    exit();
}

/* ---------------- SUCCESS MESSAGE ---------------- */
if (isset($_GET['success'])) {
    $success_map = [
        "added"=>"Event added successfully!",
        "updated"=>"Event updated successfully!",
        "deleted"=>"Event deleted successfully!"
    ];
    $msg = $success_map[$_GET['success']] ?? "";
}

$events = $conn->query("SELECT * FROM events ORDER BY id DESC");

/* Helper function to truncate text */
function truncateText($text, $max = 50){
    if(strlen($text) <= $max) return htmlspecialchars($text);
    return htmlspecialchars(substr($text, 0, $max)).'...';
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Manage Events</title>
<style>
body{font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0;}
.container{max-width:1200px; margin:30px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 0 15px rgba(0,0,0,0.1);}
h2{text-align:center; color:#dc3545; margin-bottom:20px;}
.add-btn, .bulk-btn { padding:10px 20px; background:#C00000; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-bottom:20px; margin-right:10px;}
.add-btn:hover, .bulk-btn:hover { background:#006626; }
table{width:100%; border-collapse: collapse; box-shadow:0 0 10px rgba(0,0,0,0.1);}
th{background:#008736; color:white; padding:12px; text-align:center;}
td{padding:12px; text-align:center; border-bottom:1px solid #ddd; vertical-align: top;}
tr:hover{background:#f9f2f2;}
table td, table th {
    vertical-align: middle; 
    text-align: center;
}

td.description-column {
    text-align: left;         
    max-width: 300px;        
    word-wrap: break-word;    
    white-space: normal;
}

td.actions {
    border-bottom: none;
    text-align: center;        
}

td.actions .icon-btn {
    display: inline-flex;       
    align-items: center;
    justify-content: center;
    padding: 6px;
    border-radius: 6px;
    text-decoration: none;
    margin: 0 2px;             
}

img{width:80px; border-radius:5px;}
.actions{display:flex; justify-content:center; gap:8px;}
.actions a{display:flex; align-items:center; justify-content:center; padding:6px; border-radius:6px; text-decoration:none;}
.icon-btn{border:none; font-size:16px; cursor:pointer;}
.icon-btn.edit{color:#28a745; background:#e6f9e6;}
.icon-btn.delete{color:#dc3545; background:#ffecec;}
.icon-btn.edit:hover{background:#d4f4d4;}
.icon-btn.delete:hover{background:#ffd6d6;}
.toast{position: fixed; top:100px; left:50%; transform: translateX(-50%); padding:14px 20px; border-radius:8px; color:white; font-weight:600; animation: slideIn .4s ease;}
.success{background:#28a745;}
@keyframes slideIn{from{opacity:0; transform:translate(-50%,-20px);} to{opacity:1; transform:translate(-50%,0);}}

/* MODAL */
.modal{display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color: rgba(0,0,0,0.5);}
.modal-content{background:#fafafa; margin:80px auto; padding:20px 30px; border-radius:8px; width:90%; max-width:600px; position:relative; box-shadow:0 0 20px rgba(0,0,0,0.3); text-align:center;}
.close-btn{color:#C00000; font-size:24px; font-weight:bold; position:absolute; top:10px; right:15px; cursor:pointer;}
.close-btn:hover{color:#008736;}
input, textarea{width:100%; padding:10px; margin:8px 0; box-sizing:border-box;}
textarea{resize: vertical; min-height:60px;}
button.submit-btn{background:#008736; color:white; padding:10px 18px; border:none; border-radius:5px; cursor:pointer; font-weight:600; margin-top:10px;}
button.submit-btn:hover{background:#006626;}
@media screen and (max-width:768px){ table, th, td{font-size:14px;} }
.table-responsive{overflow-x:auto;}

#descModal .modal-content {
    max-width: 500px;
    max-height: 70vh;  
    overflow-y: auto; 
    padding: 20px;
    text-align: left;
    word-wrap: break-word; 
    white-space: normal;  
}
#descModal p {
    margin: 0;
    white-space: pre-wrap; 
}
</style>
</head>
<body>

<div class="container">

<?php if($msg): ?>
<div class="toast success">✅ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<h2>Manage Events</h2>

<button class="add-btn" onclick="openModal()"><span style="font-size:18px; font-weight:700;"> + </span> Add New Event</button>
<form method="POST" style="display:inline;" id="bulkForm">
<button type="submit" name="bulk_delete" class="bulk-btn" onclick="return confirm('Delete selected events?')">🗑 Delete Selected</button>

<div class="table-responsive">
<table>
<tr>
    <th><input type="checkbox" id="selectAll"></th>
    <th>Image</th>
    <th>Title</th>
    <th>Description</th>
    <th>Date</th>
    <th>Time</th>
    <th>Actions</th>
</tr>

<?php while($row=$events->fetch_assoc()): ?>
<tr>
    <td><input type="checkbox" name="event_ids[]" value="<?= $row['id'] ?>"></td>
    <td>
        <?php if($row['image']): ?>
            <img src="../uploads/gallery/events/<?= htmlspecialchars($row['image']) ?>">
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td class="description-column">
        <?php 
            $desc = htmlspecialchars($row['description']);
            if(strlen($desc) > 50){
                echo truncateText($desc,50).' <a href="#" class="read-more" data-desc="'.htmlspecialchars($desc).'">Read More</a>';
            } else {
                echo $desc;
            }
        ?>
    </td>
    <td><?= htmlspecialchars($row['event_date']) ?></td>
    <td><?= htmlspecialchars($row['event_time']) ?></td>
    <td class="actions">
        <a class="icon-btn edit" href="manage-events.php?edit=<?= $row['id'] ?>" title="Edit"><i class="fas fa-edit"></i></a>
        <a class="icon-btn delete" href="manage-events.php?delete=<?= $row['id'] ?>" title="Delete" onclick="return confirm('Delete this event?')"><i class="fas fa-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</form>

</div>

<!-- ADD/EDIT MODAL -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle"><?= $editData ? "Edit Event" : "Add Event" ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <?php if($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
            <?php endif; ?>
            <input type="text" name="title" placeholder="Event Title" required value="<?= htmlspecialchars($editData['title'] ?? '') ?>">
            <textarea name="description" placeholder="Description" required><?= htmlspecialchars($editData['description'] ?? '') ?></textarea>
            <input type="date" name="event_date" required value="<?= htmlspecialchars($editData['event_date'] ?? '') ?>">
            <input type="time" name="event_time" value="<?= htmlspecialchars($editData['event_time'] ?? '') ?>">
            <input type="file" name="image">
            <?php if($editData && $editData['image']): ?>
                <img src="../uploads/gallery/events/<?= htmlspecialchars($editData['image']) ?>" width="100" style="margin-top:10px;">
            <?php endif; ?>
            <button type="submit" class="submit-btn" name="<?= $editData ? 'update_event' : 'add_event' ?>"><?= $editData ? 'Update Event' : 'Add Event' ?></button>
        </form>
    </div>
</div>

<!-- READ MORE MODAL -->
<div id="descModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeDescModal()">&times;</span>
        <p id="fullDesc"></p>
    </div>
</div>

<script>
// Add/Edit modal
function openModal(){ document.getElementById('eventModal').style.display = 'block'; }
function closeModal(){ document.getElementById('eventModal').style.display = 'none'; }

// Read More modal
function openDescModal(text){
    document.getElementById('fullDesc').textContent = text;
    document.getElementById('descModal').style.display = 'block';
}
function closeDescModal(){ document.getElementById('descModal').style.display = 'none'; }

// Close modals if clicked outside
window.onclick = function(event){
    if(event.target==document.getElementById('eventModal')) closeModal();
    if(event.target==document.getElementById('descModal')) closeDescModal();
}

// Select All checkboxes
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
selectAll.addEventListener('click', function(){ checkboxes.forEach(cb=>cb.checked=this.checked); });
checkboxes.forEach(cb => cb.addEventListener('change', function(){
    selectAll.checked = Array.from(checkboxes).every(c=>c.checked);
}));

// Auto-hide toast
setTimeout(()=> { document.querySelectorAll('.toast').forEach(t=>t.remove()); },3000);

// Open modal if editing
<?php if($editData): ?> openModal(); <?php endif; ?>

// Read More click
document.querySelectorAll('.read-more').forEach(link=>{
    link.addEventListener('click', function(e){
        e.preventDefault();
        openDescModal(this.dataset.desc);
    });
});
</script>

</body>
</html>
