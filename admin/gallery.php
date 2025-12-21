<?php
include 'navbar.php';
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'] ?? '';
    $files = $_FILES['images'] ?? null;

    if (!$event_name || !$files) {
        $error = "Please enter an event name and select at least one image.";
    } else {
        // Create folder for event
        $event_folder = '../uploads/gallery/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event_name);
        if (!is_dir($event_folder)) {
            mkdir($event_folder, 0777, true);
        }

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
                    // Save in DB: use event_name as title
                    mysqli_query($conn, "INSERT INTO gallery (title, filename, event_name) VALUES ('$event_name', '$filename', '$event_name')");
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Gallery Images</title>
    <style>
        body { font-family:'Segoe UI', sans-serif; background:#f3f3f3; margin:0; padding:0; }
        .container { max-width:500px; margin:40px auto; background:white; padding:25px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.08);}
        label { display:block; margin-bottom:8px; font-weight:600;}
        input[type="text"], input[type="file"] { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
        button { padding:10px 20px; background:#008736; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
        button:hover { background:#006626; }
        .success { background:#28a745; color:white; padding:10px; border-radius:6px; margin-bottom:15px; }
        .error { background:#dc3545; color:white; padding:10px; border-radius:6px; margin-bottom:15px; }
        h2 { color: b30000;}
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Gallery Images</h2>

    <?php if(isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="event">Event Name</label>
        <input type="text" name="event_name" id="event" placeholder="Enter event name" required>

        <label for="images">Select Images</label>
        <input type="file" name="images[]" id="images" multiple required>

        <button type="submit">Upload</button>
    </form>
</div>
</body>
</html>
