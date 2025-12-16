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
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📩 Contact Messages</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

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

        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['subject']) ?></td>
                <td class="message"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
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

</body>
</html>
