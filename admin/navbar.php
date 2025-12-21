<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="logo">
        <a href="dashboard.php">
            <img src="../images/logo1.jpg" alt="Munikar Lyamha Pucha Logo" class="logo-img">
        </a>
    </div>

    <ul>
        <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Messages</a></li>
        <li><a href="gallery.php" class="<?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>">Gallery</a></li>
        <li><a href="logout.php" class="logout-btn">Logout</a></li>
    </ul>
</nav>

<style>
/* NAVBAR */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 40px;
    background: #C00000;
}

/* Active link */
.navbar ul li a.active {
    color: #FFFFFF;
    font-weight: 700;
    border-bottom: 2px solid white;
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
}

.logo-img {
    height: 65px;
    width: 65px;
    object-fit: cover;
}

/* Navbar menu */
.navbar ul {
    list-style: none;
    display: flex;
    gap: 35px;
    align-items: center;
}

/* Navbar links */
.navbar ul li a {
    color: white;
    text-decoration: none;
    font-size: 17px;
    font-weight: 500;
    transition: 0.3s;
}

/* Hover effect */
.navbar ul li a:hover {
    color: #ffcc00;
}

/* Logout button style */
/* Logout button style */
.logout-btn {
    background: #FFD200 !important;
    color: #C00000 !important;
    padding: 6px 15px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 17px;
    text-decoration: none;
    display: inline-block;
    transition: 0.3s;
}

.logout-btn:hover {
    background: #ffe6e6 !important;
    color: #C00000 !important;
}

</style>
