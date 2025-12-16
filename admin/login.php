<?php
session_start();
include '../includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM admin_users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .login-box {
            max-width: 350px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        input, button {
            width: 100%;
            height: 44px;          /* SAME HEIGHT */
            padding: 0 14px;       /* Horizontal padding only */
            margin-bottom: 15px;
            font-size: 15px;
            border-radius: 6px;
            box-sizing: border-box;
        }

        input {
            border: 1px solid #ccc;
        }

        button {
            background: #b30000;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        button:hover {
            background: #8f0000;
        }
        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            width: 100%;
            height: 44px;
            padding: 0 50px 0 14px; /* space for Show button */
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 13px;
            color: #b30000;
            font-weight: 600;
            user-select: none;
        }

        .toggle-password:hover {
            color: #8f0000;
        }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword()">Show</span>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const toggle = document.querySelector(".toggle-password");

    if (password.type === "password") {
        password.type = "text";
        toggle.textContent = "Hide";
    } else {
        password.type = "password";
        toggle.textContent = "Show";
    }
}
</script>

</body>
</html>
