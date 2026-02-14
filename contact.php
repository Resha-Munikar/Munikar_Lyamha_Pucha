<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- CONTACT PAGE -->
<section class="contact-page">

    <!-- INTRO TEXT -->
    <div class="contact-intro">
        <h1>Contact Us</h1>
        <p>
            We would love to hear from you. Whether it’s about cultural events, 
            jatras, youth participation, or collaboration — feel free to reach out.
        </p>
    </div>

    <?php
    // Check for success or error messages via GET
    $toastMessage = '';
    $toastType = '';

    if (isset($_GET['success'])) {
        $toastMessage = "✅ Thank you! Your message has been sent successfully.";
        $toastType = "success";
    } elseif (isset($_GET['error'])) {
        $toastMessage = "❌ Something went wrong. Please try again.";
        $toastType = "error";
    }
    ?>

    <!-- FORM + MAP -->
    <div class="contact-layout">

        <!-- CONTACT FORM -->
        <div class="contact-form">
            <h2>Send Us a Message</h2>

            <form action="send-message.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="text" name="subject" placeholder="Subject">
                <textarea name="message" placeholder="Your Message" required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>

        <!-- MAP -->
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps?q=Maligaun,Kathmandu&output=embed"
                loading="lazy">
            </iframe>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Toast Notification -->
<?php if ($toastMessage): ?>
<div class="toast <?= $toastType ?>">
    <?= htmlspecialchars($toastMessage) ?>
</div>
<?php endif; ?>

<style>
.toast {
    position: fixed;
    top: 100px;
    left: 50%;
    transform: translateX(-50%);
    padding: 14px 20px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10000;
    animation: slideIn 0.4s ease;
}
.toast.success { background: #28a745; }
.toast.error { background: #dc3545; }
@keyframes slideIn {
    from { opacity: 0; transform: translate(-50%, -20px); }
    to   { opacity: 1; transform: translate(-50%, 0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.querySelector('.toast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = "opacity 0.5s";
            toast.style.opacity = "0";
            setTimeout(() => toast.remove(), 500);
        }, 3500); // hide after 3.5 seconds
    }
});
</script>
