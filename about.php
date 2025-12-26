<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<style>
/* ===== ABOUT PAGE STYLES ===== */

.about-hero {
    height: 70vh;
    background: linear-gradient(
        rgba(0, 0, 0, 0.55),
        rgba(0, 0, 0, 0.55)
    ),
    url('images/about-hero.jpg') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
    padding: 20px;
}

.about-hero h1 {
    font-size: 3rem;
    margin-bottom: 10px;
}

.about-hero p {
    font-size: 1.2rem;
    letter-spacing: 1px;
}

/* CONTAINER */
.about-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

/* SECTION */
.about-section {
    margin-bottom: 60px;
}

.about-section h2 {
    color: #C00000;
    margin-bottom: 15px;
    font-size: 2rem;
}

.about-section p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #333;
}

/* MISSION & VISION */
.mission-vision {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.mv-box {
    background: #f8f8f8;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.mv-box h3 {
    color: #008736;
    margin-bottom: 10px;
}

/* WHAT WE DO */
.activities {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.activity {
    background: #fff;
    padding: 22px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.activity span {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 10px;
}

/* VALUES */
.values {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.value {
    background: #f3f3f3;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    font-weight: 600;
}

/* JOIN US */
.join-us {
    background: linear-gradient(135deg, #C00000, #008736);
    color: #fff;
    padding: 50px 30px;
    border-radius: 16px;
    text-align: center;
}

.join-us h2 {
    color: #fff;
}

.join-us p {
    max-width: 700px;
    margin: 15px auto 0;
    font-size: 1.1rem;
}

/* RESPONSIVE */
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
<section class="about-hero">
    <div>
        <h1>About Us</h1>
        <p>Tied to Roots • Preserving Culture • Uniting Youth</p>
    </div>
</section>

<!-- ABOUT CONTENT -->
<div class="about-container">

    <!-- WHO WE ARE -->
    <section class="about-section">
        <h2>Who We Are</h2>
        <p>
            <strong>Munikar Lyamha Pucha</strong> is a community-based youth organization
            committed to preserving cultural heritage, strengthening unity,
            and empowering young individuals to actively contribute to society.
            Rooted in tradition while embracing modern values, we bridge generations
            by honoring our roots and shaping a progressive future.
        </p>
        <p>
            Through cultural programs, social initiatives, and community engagement,
            we inspire pride in identity, responsibility, and togetherness.
        </p>
    </section>

    <!-- MISSION & VISION -->
    <section class="about-section mission-vision">
        <div class="mv-box">
            <h3>Our Mission</h3>
            <p>
                To preserve and promote cultural traditions, unite youth through
                meaningful engagement, encourage leadership and responsibility,
                and support social, cultural, and educational development.
            </p>
        </div>

        <div class="mv-box">
            <h3>Our Vision</h3>
            <p>
                A strong and united community where youth are culturally aware,
                socially responsible, and actively shaping a harmonious society.
            </p>
        </div>
    </section>

    <!-- WHAT WE DO -->
    <section class="about-section">
        <h2>What We Do</h2>
        <div class="activities">
            <div class="activity">
                <span>🎭</span>
                Cultural Programs & Festivals
            </div>
            <div class="activity">
                <span>🤝</span>
                Community Service Initiatives
            </div>
            <div class="activity">
                <span>🎓</span>
                Youth Engagement & Leadership
            </div>
            <div class="activity">
                <span>🎉</span>
                Events & Awareness Campaigns
            </div>
        </div>
    </section>

    <!-- VALUES -->
    <section class="about-section">
        <h2>Our Core Values</h2>
        <div class="values">
            <div class="value">Unity & Respect</div>
            <div class="value">Cultural Preservation</div>
            <div class="value">Youth Empowerment</div>
            <div class="value">Community Responsibility</div>
        </div>
    </section>

    <!-- JOIN US -->
    <section class="join-us">
        <h2>Join Us</h2>
        <p>
            Munikar Lyamha Pucha welcomes individuals who share a passion
            for culture, community, and positive change. Together,
            we can preserve our roots while building a brighter future.
        </p>
    </section>

</div>

<?php include 'includes/footer.php'; ?>
