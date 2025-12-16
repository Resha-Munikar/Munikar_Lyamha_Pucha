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

    <?php if (isset($_GET['success'])): ?>
        <p id="success-message" class="success-message" style="
            color: #155724;
            background: #d4edda;
            padding: 14px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 500;
        ">
            ✅ Thank you! Your message has been sent successfully.
        </p>
    <?php endif; ?>

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
