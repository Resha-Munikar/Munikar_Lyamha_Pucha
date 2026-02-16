<?php
include '../includes/db.php';
include 'navbar.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");
// Count unread messages
$unreadResult = mysqli_query($conn, "SELECT COUNT(*) AS unread_count FROM messages WHERE status='unread'");
$unreadRow = mysqli_fetch_assoc($unreadResult);
$unreadCount = $unreadRow['unread_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f3f3f3;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h2 {
            margin: 0;
            color: #b30000;
            text-align:center;
        }

        .logout {
            text-decoration: none;
            background: #b30000;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
        }

        .logout:hover {
            background: #8f0000;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* IMPORTANT */
        }

        thead th {
            background: #008736;
            color: #ffffff;
            padding: 14px 12px;
            font-size: 14px;
            text-align: left;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 12px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            word-wrap: break-word;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

       /* COLUMN WIDTH CONTROL */

        /* Checkbox */
        th:nth-child(1),
        td:nth-child(1) {
            width: 50px;
            text-align: center;
        }

        /* Name */
        th:nth-child(2),
        td:nth-child(2) {
            width: 14%;
        }

        /* Email */
        th:nth-child(3),
        td:nth-child(3) {
            width: 22%;
        }

        /* Subject */
        th:nth-child(4),
        td:nth-child(4) {
            width: 14%;
        }

        /* Message (View button) */
        th:nth-child(5),
        td:nth-child(5) {
            width: 12%;
            text-align: center;
        }

        /* Date */
        th:nth-child(6),
        td:nth-child(6) {
            width: 14%;
            white-space: nowrap;
        }

        /* Action */
        th:nth-child(7),
        td:nth-child(7) {
            width: 10%;
            text-align: center;
        }


        /* Message cell */
        td.message {
            line-height: 1.5;
        }

        /* Delete Button */
        .delete {
            background: #dc3545;
            color: #fff;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .delete:hover {
            background: #b02a37;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }
        /* SEARCH */
        .search-box {
            width: 300px;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        /* TABLE */
        .table-wrapper {
            max-height: 500px;
            overflow-y: auto;
        }

        /* UNREAD */
        tr.unread {
            background: #fff4f4;
            font-weight: bold;
        }

        /* BUTTONS */
        .view-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .view-btn:hover {
            background: #218838;
        }

        .view-btn.active {
            background: #ffc107; /* toggle color when active */
            color: #333;
        }

        /* MODAL BACKGROUND */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            animation: fadeIn 0.3s;
        }

        /* MODAL CONTENT */
        .modal-content {
            background: #fff;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            margin: 60px auto;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .modal-header {
            background: #28a745;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .modal-header .close {
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .modal-header .close:hover {
            transform: rotate(90deg);
        }

        /* BODY */
        .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .modal-body label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .modal-body input,
        .modal-body textarea {
            width: 95%;           /* slightly smaller than full width */
            max-width: 590px;     /* optional: limit maximum width */
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            display: block;       /* ensures proper spacing */
        }

        .modal-body textarea {
            height: 120px;
            resize: vertical;
        }

        /* FOOTER */
        .modal-footer {
            padding: 15px 20px;
            text-align: right;
            background: #f7f7f7;
        }

        .modal-footer .send-btn {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 8px;
            transition: 0.3s;
        }

        .modal-footer .send-btn:hover {
            background: #218838;
        }

        .modal-footer .close-btn {
            padding: 8px 16px;
            background: #ccc;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .modal-footer .close-btn:hover {
            background: #b3b3b3;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        @keyframes slideIn {
            from {transform: translateY(-30px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        tr.replied {
            background: #f1f8f1;  
        }
        /* ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }

        .action-buttons a,
        .action-buttons button {
            white-space: nowrap;
        }
        .action-buttons .delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }

        .action-buttons .delete:hover {
            background: #b02a37;
        }
        /* ICON BUTTONS */
        .icon-btn {
            border: none;
            background: #f4f4f4;
            font-size: 16px;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn.reply {
            color: #28a745;
        }

        .icon-btn.delete {
            color: #dc3545;
            background: #ffecec; /* light red bg */
        }

        .icon-btn.reply:hover {
            background: #e6f6ea;
        }

        .icon-btn.delete:hover {
            background: #ffd6d6;
        }

        .icon-btn.disabled {
            color: #999;
            background: #eee;
            cursor: not-allowed;
        }

        /* BULK DELETE BUTTON */
        .bulk-delete-btn {
            background: #dc3545;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .bulk-delete-btn i {
            margin-right: 5px;
        }
        /* CHECKBOX COLUMN */
        .check-col {
            text-align: center;
        }

        .check-col input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .message-preview {
            display: flex;
            align-items: center;
            gap: 8px; 
        }

        .preview-text {
            flex: 1; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 650px;
            padding: 14px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideInRight 0.4s ease;
        }

        .toast.success {
            background: #28a745;
        }

        .toast.error {
            background: #dc3545;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

    </style>
</head>
<body>
<?php if (isset($_GET['replied'])): ?>
    <div class="toast success">
        <i class="fas fa-check-circle"></i>
        Reply sent successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="toast error">
        <i class="fas fa-times-circle"></i>
        Failed to send reply. Try again.
    </div>
<?php endif; ?>

<div class="container">
        <h2>📩 Contact Messages</h2>

    <input type="text" id="searchInput" placeholder="🔍 Search messages..." class="search-box">

    <div class="table-wrapper">
        <form action="bulk-delete.php" method="POST">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <button class="bulk-delete-btn"
                onclick="return confirm('Delete selected messages?')">
                <i class="fas fa-trash"></i> Delete Selected
            </button>

            <div style="display: flex; align-items: center; gap: 10px;">
                <span id="unreadBadge" style="
                    background: #dc3545;
                    color: white;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 600;
                ">
                    <i class="fas fa-envelope"></i> <?= $unreadCount ?> Unread
                </span>

                <!-- Filter unread button -->
                <button id="filterUnreadBtn" type="button" class="view-btn" style="background:#28a745; color:white;">
                    Show Unread
                </button>
            </div>
        </div>

            <table>
                <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody id="messageTable">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="
                            <?= $row['status'] === 'unread' ? 'unread' : '' ?>
                            <?= $row['replied'] === 'yes' ? 'replied' : '' ?>
                        ">
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $row['id'] ?>">
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td>
                            <div class="message-preview">
                                <span class="preview-text">
                                    <?php 
                                        $preview = strlen($row['message']) > 50 ? substr($row['message'], 0, 50) . '...' : $row['message']; 
                                        echo htmlspecialchars($preview);
                                    ?>
                                </span>
                                <button type="button" class="view-btn"
                                    onclick="openModal(`<?= htmlspecialchars(addslashes($row['message'])) ?>`, <?= $row['id'] ?>)">
                                    View
                                </button>
                            </div>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <div class="action-buttons">

                                <!-- REPLY -->
                                <?php if ($row['replied'] === 'no'): ?>
                                    <button type="button" class="icon-btn reply"
                                        onclick="openReplyModal(
                                            '<?= htmlspecialchars($row['email']) ?>',
                                            '<?= htmlspecialchars($row['subject']) ?>',
                                            <?= $row['id'] ?>
                                        )">
                                        <i class="fas fa-reply"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="icon-btn reply disabled" title="Already replied" disabled>
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- DELETE -->
                                <a class="icon-btn delete"
                                href="delete-message.php?id=<?= $row['id'] ?>"
                                title="Delete"
                                onclick="return confirm('Delete this message?')">
                                <i class="fas fa-trash"></i>
                                </a>

                            </div>
                        </td>
                        
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    </div>

</div>
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📩 Message</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p id="modalText"></p>
        </div>
        <div class="modal-footer">
            <button class="close-btn" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<div id="replyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✉️ Reply to Message</h3>
            <span class="close" onclick="closeReplyModal()">&times;</span>
        </div>

        <div class="modal-body">
            <form id="replyForm" action="send-reply.php" method="POST">
                <input type="hidden" name="message_id" id="replyMessageId">

                <label for="replyEmail">Email</label>
                <input type="email" name="to_email" id="replyEmail" readonly>

                <label for="replySubject">Subject</label>
                <input type="text" name="subject" id="replySubject" required>

                <label for="reply_message">Reply</label>
                <textarea name="reply_message" id="reply_message" required></textarea>
            </form>
        </div>

        <div class="modal-footer">
            <button class="send-btn" type="submit" form="replyForm">Send Reply</button>
            <button class="close-btn" onclick="closeReplyModal()">Cancel</button>
        </div>

    </div>
</div>

<script>
function openModal(message, id) {
    document.getElementById('modalText').innerText = message;
    document.getElementById('messageModal').style.display = 'block';

    // Mark as read via AJAX
    fetch('mark-read.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            // Update the unread count badge
            let badge = document.getElementById('unreadBadge');
            let count = parseInt(badge.innerText);
            if (count > 0) {
                badge.innerText = (count - 1) + " Unread";
            }
        });
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}


function openReplyModal(email, subject, id) {
    document.getElementById('replyEmail').value = email;
    document.getElementById('replySubject').value = 'Re: ' + subject;
    document.getElementById('replyMessageId').value = id;
    document.getElementById('replyModal').style.display = 'block';
}

function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
}
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => {
        cb.checked = this.checked;
    });
});
function confirmDelete(id) {
    if (confirm('Delete this message?')) {
        window.location.href = 'delete-message.php?id=' + id;
    }
}
const filterBtn = document.getElementById('filterUnreadBtn');
let showingUnreadOnly = false;

filterBtn.addEventListener('click', function() {
    const rows = document.querySelectorAll('#messageTable tr');
    showingUnreadOnly = !showingUnreadOnly;

    if (showingUnreadOnly) {
        filterBtn.innerText = "Show All";
        filterBtn.classList.add('active');
        rows.forEach(row => {
            row.style.display = row.classList.contains('unread') ? '' : 'none';
        });
    } else {
        filterBtn.innerText = "Show Unread";
        filterBtn.classList.remove('active');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
});

// SEARCH FUNCTIONALITY
document.getElementById('searchInput').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#messageTable tr');

    rows.forEach(row => {
        const matchesSearch = row.innerText.toLowerCase().includes(value);
        const isUnreadHidden = showingUnreadOnly && !row.classList.contains('unread');

        row.style.display = matchesSearch && !isUnreadHidden ? '' : 'none';
    });
});
setTimeout(() => {
    document.querySelectorAll('.toast').forEach(t => t.remove());
}, 3500);
</script>

</body>
</html>
