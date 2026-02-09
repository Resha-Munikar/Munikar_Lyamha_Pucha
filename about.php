<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<style>
/* ----------------- GENERAL ----------------- */
.about-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
}

h2 {
    font-weight: 600;
}

/* ----------------- HERO SECTION ----------------- */
.about-hero {
    width: 100%; /* full width */
    padding: 150px 20px; /* increased height */
    color: #fff;
    text-align: center;
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/hero-bg.jpg') center/cover no-repeat;
    border-radius: 0; /* remove rounding if you want full-width edge-to-edge */
    box-sizing: border-box;
}

.about-hero h1 {
    font-size: 3rem;
    margin-bottom: 15px;
}

.about-hero p {
    font-size: 1.2rem;
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ----------------- FACES OF COMMUNITY ----------------- */
.faces-section {
    padding: 50px 20px;
    text-align: center;
}

.faces-section h2 {
    font-size: 2.6rem;
    color: #C00000;
    margin-bottom: 10px;
}

.faces-subtitle {
    font-size: 1.1rem;
    color: #444;
    margin-bottom: 40px;
}

.faces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
}

.face-card {
    background: #fff;
    padding: 25px 20px;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s, opacity 0.6s;
    opacity: 0;
    transform: translateY(30px);
}

.face-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.face-avatar {
    font-size: 3rem;
    margin-bottom: 15px;
}

.face-card h3 {
    color: #008736;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

/* ----------------- CULTURAL IDENTITY ----------------- */
.identity-section {
    padding: 70px 20px;
    background: #f9f9f9;
    text-align: center;
    margin-bottom: 60px;
    border-radius: 16px;
}

.identity-section h2 {
    font-size: 2.6rem;
    color: #C00000;
    margin-bottom: 10px;
}

.identity-subtitle {
    font-size: 1.1rem;
    color: #444;
    margin-bottom: 40px;
}

.identity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    max-width: 1100px;
    margin: auto;
}

.identity-card {
    background: white;
    padding: 30px 20px;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s, opacity 0.6s;
    opacity: 0;
    transform: translateY(30px);
}

.identity-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.identity-card span {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 10px;
}

.identity-card h3 {
    color: #008736;
    margin-bottom: 10px;
}

/* ----------------- MISSION & VISION ----------------- */
.mission-vision {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.mv-box {
    background: #f8f8f8;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    opacity: 0;
    transform: translateY(30px);
    transition: transform 0.5s, opacity 0.5s;
}

.mv-box.visible {
    opacity: 1;
    transform: translateY(0);
}

.mv-box h3 {
    color: #008736;
    margin-bottom: 10px;
}

/* ----------------- JOIN US ----------------- */
.join-us {
    background: linear-gradient(135deg, #C00000, #008736);
    color: #fff;
    padding: 60px 30px;
    border-radius: 16px;
    text-align: center;
    margin-bottom: 60px;
}

.join-us h2 {
    font-size: 2.6rem;
    margin-bottom: 20px;
}

.join-us p {
    max-width: 700px;
    margin: 0 auto 25px;
    font-size: 1.1rem;
    line-height: 1.6;
}

.join-btn {
    background: white;
    color: #b30000;
    padding: 12px 25px;
    font-size: 1.1rem;
    border-radius: 6px;
    transition: all 0.3s;
    font-weight: bold;
    text-decoration: none;
}

.join-btn:hover {
    background: #ffcc00;
    color: #b30000;
}

/* ----------------- RESPONSIVE ----------------- */
@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2.2rem;
    }
    .about-hero p {
        font-size: 1rem;
    }
}
</style>

<!-- HERO SECTION -->
<div class="about-hero">
    <h1>Welcome to Munikar Lyamha Pucha</h1>
    <p>We are a community dedicated to preserving culture, empowering youth, and fostering unity through meaningful engagement and service.</p>
</div>
<div class="about-container">
    <!-- FACES OF COMMUNITY -->
    <section class="faces-section">
        <h2>Faces of Our Community</h2>
        <p class="faces-subtitle">Meet the people who keep our culture alive</p>

        <div class="faces-grid">
            <div class="face-card">
                <div class="face-avatar">👩</div>
                <h3>Youth Member</h3>
                <p>"Being part of this group connects me with my culture and community."</p>
            </div>
            <div class="face-card">
                <div class="face-avatar">👨‍🦳</div>
                <h3>Community Elder</h3>
                <p>"This organization preserves our traditions for future generations."</p>
            </div>
            <div class="face-card">
                <div class="face-avatar">🧑‍🎓</div>
                <h3>Volunteer</h3>
                <p>"I learned leadership and teamwork through community service."</p>
            </div>
            <div class="face-card">
                <div class="face-avatar">👨‍🏫</div>
                <h3>Youth Leader</h3>
                <p>"Together, we inspire unity and responsibility among young people."</p>
            </div>
        </div>
    </section>

    <!-- CULTURAL IDENTITY -->
    <section class="identity-section">
        <h2>Our Cultural Identity</h2>
        <p class="identity-subtitle">What defines Munikar Lyamha Pucha</p>

        <div class="identity-grid">
            <div class="identity-card">
                <span>🪔</span>
                <h3>Tradition</h3>
                <p>Preserving rituals, festivals, and heritage for future generations.</p>
            </div>
            <div class="identity-card">
                <span>👥</span>
                <h3>Unity</h3>
                <p>Bringing youth together through culture, respect, and cooperation.</p>
            </div>
            <div class="identity-card">
                <span>🌱</span>
                <h3>Youth</h3>
                <p>Encouraging leadership, responsibility, and social involvement.</p>
            </div>
            <div class="identity-card">
                <span>🤲</span>
                <h3>Service</h3>
                <p>Working for the community through social and cultural initiatives.</p>
            </div>
        </div>
    </section>

    <!-- MISSION & VISION -->
    <section class="about-section mission-vision">
        <div class="mv-box">
            <h3>Our Mission</h3>
            <p>To preserve and promote cultural traditions, unite youth through meaningful engagement, encourage leadership and responsibility, and support social, cultural, and educational development.</p>
        </div>
        <div class="mv-box">
            <h3>Our Vision</h3>
            <p>A strong and united community where youth are culturally aware, socially responsible, and actively shaping a harmonious society.</p>
        </div>
    </section>

    <!-- JOIN US -->
    <section class="join-us">
        <h2>Be a part of our Community</h2>
        <p>Munikar Lyamha Pucha welcomes individuals who share a passion for culture, community, and positive change. Together, we can preserve our roots while building a brighter future.</p>
        <a href="contact.php" class="join-btn">Get Involved</a>
    </section>

</div>

<!-- ----------------- SCROLL ANIMATION SCRIPT ----------------- -->
<script>
function revealOnScroll() {
    const elements = document.querySelectorAll('.face-card, .identity-card, .mv-box');
    const windowHeight = window.innerHeight;
    elements.forEach(el => {
        const elementTop = el.getBoundingClientRect().top;
        if(elementTop < windowHeight - 100){
            el.classList.add('visible');
        }
    });
}

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);
</script>

<?php include 'includes/footer.php'; ?>

