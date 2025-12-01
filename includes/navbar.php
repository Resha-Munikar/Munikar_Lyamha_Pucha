<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="images/logo1.jpg" alt="Munikar Lyamha Pucha Logo" class="logo-img">
        </a>
    </div>

    <ul>
        <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
        <li><a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About</a></li>
        <li><a href="events.php" class="<?php echo ($current_page == 'events.php') ? 'active' : ''; ?>">Events</a></li>
        <li><a href="gallery.php" class="<?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>">Gallery</a></li>
        <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
    </ul>
</nav>
