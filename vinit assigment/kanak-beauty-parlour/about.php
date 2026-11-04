<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();
$settings = getSettings($pdo);
$pageTitle = 'About Us | Kanak Beauty Parlour Betul';
$pageDesc = 'Meet the team and story behind Kanak Beauty Parlour, a beauty and makeup studio in Betul, Madhya Pradesh.';
$currentPage = 'about';
include 'includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="hero-content text-center">
            <p class="hero-subtitle">Our Story</p>
            <h1 class="hero-title">A Place to Feel Beautiful</h1>
            <p class="hero-description">Personal care, professional artistry, and a warm welcome in Betul.</p>
        </div>
    </div>
</section>
<section class="section-padding">
    <div class="container about-layout">
        <div class="about-image-wrap">
            <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&amp;fit=crop&amp;w=1000&amp;q=80" alt="A calm beauty studio setting" loading="lazy">
        </div>
        <div class="about-content">
            <span class="sub-label">Welcome to Kanak</span>
            <h2>Beauty that feels like you</h2>
            <p>Kanak Beauty Parlour was created to make professional beauty care feel personal. We take time to understand your preferences, your occasion, and the details that matter to you.</p>
            <p>Owner and artist <?= htmlspecialchars($settings['owner_name'] ?? 'Kavita Kawadker', ENT_QUOTES, 'UTF-8') ?> brings a thoughtful approach to bridal and occasion makeup, hairstyling, skincare, and everyday beauty services.</p>
            <p>Our goal is simple: help every client leave feeling comfortable, cared for, and confident.</p>
            <a class="btn btn-gold" href="appointment.php">Plan Your Visit</a>
        </div>
    </div>
</section>
<section class="section-padding bg-ivory">
    <div class="container">
        <div class="section-header">
            <span class="sub-label">What you can expect</span>
            <h2>Thoughtful Care at Every Step</h2>
            <span class="gold-line"></span>
        </div>
        <div class="contact-info-grid">
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-heart" aria-hidden="true"></i></div><h4>Personal Consultation</h4><p>We listen first and tailor each look to your preferences and occasion.</p></article>
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></div><h4>Professional Artistry</h4><p>From soft and natural to celebration-ready, your style stays at the center.</p></article>
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i></div><h4>A Welcoming Space</h4><p>Enjoy attentive care in a relaxed parlour environment in Betul.</p></article>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
