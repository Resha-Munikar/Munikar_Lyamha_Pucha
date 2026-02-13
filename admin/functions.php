<?php
// admin/functions.php
if (!function_exists('deleteEvent')) {
    function deleteEvent($id, $conn){
        $stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if($result['image'] && file_exists("../uploads/gallery/events/".$result['image'])){
            unlink("../uploads/gallery/events/".$result['image']);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
