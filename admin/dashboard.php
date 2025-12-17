<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
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

        .header h2 {
            margin: 0;
            color: #b30000;
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
            background: #b30000;
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
        th:nth-child(1), td:nth-child(1) { width: 16%; } /* Name */
        th:nth-child(2), td:nth-child(2) { width: 22%; } /* Email */
        th:nth-child(3), td:nth-child(3) { width: 12%; } /* Subject */
        th:nth-child(4), td:nth-child(4) { width: 28%; } /* Message */
        th:nth-child(5), td:nth-child(5) { width: 14%; } /* Date */
        th:nth-child(6), td:nth-child(6) { width: 8%;  text-align: center; } /* Action */

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

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background: #b30000;
            color: white;
            padding: 14px;
            position: sticky;
            top: 0;
            z-index: 2; /* LOWER */
        }

        tbody td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            word-wrap: break-word;
        }

        /* UNREAD */
        tr.unread {
            background: #fff4f4;
            font-weight: bold;
        }

        /* BUTTONS */
        .view-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .view-btn:hover {
            background: #0056b3;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999; /* 🔥 ADD THIS */
        }

        .modal-content {
            background: white;
            padding: 25px;
            max-width: 600px;
            margin: 100px auto;
            border-radius: 10px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 22px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📩 Contact Messages</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <input type="text" id="searchInput" placeholder="🔍 Search messages..." class="search-box">

    <div class="table-wrapper">
    <table>
        <thead>
            <tr>
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
            <tr class="<?= $row['status'] === 'unread' ? 'unread' : '' ?>">
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td>
                    <button class="view-btn"
                        onclick="openModal(`<?= htmlspecialchars(addslashes($row['message'])) ?>`, <?= $row['id'] ?>)">
                        View
                    </button>
                </td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <button class="reply-btn"
                        onclick="openReplyModal(
                            '<?= htmlspecialchars($row['email']) ?>',
                            '<?= htmlspecialchars($row['subject']) ?>',
                            <?= $row['id'] ?>
                        )">
                        Reply
                    </button>

                    <a class="delete"
                    href="delete-message.php?id=<?= $row['id'] ?>"
                    onclick="return confirm('Delete this message?')">
                    Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

</div>
<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Message</h3>
        <p id="modalText"></p>
    </div>
</div>

<script>
function openModal(message, id) {
    document.getElementById('modalText').innerText = message;
    document.getElementById('messageModal').style.display = 'block';

    // Mark as read
    fetch('mark-read.php?id=' + id);
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

/* SEARCH */
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#messageTable tr');

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value)
            ? ''
            : 'none';
    });
});
</script>

</body>
</html>
