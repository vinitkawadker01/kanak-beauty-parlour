<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();

$settings = getSettings($pdo);
$services = getActiveServices($pdo);
$reviews = getActiveReviews($pdo);
$galleryStmt = $pdo->query(
    "SELECT title, image, category FROM gallery WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC LIMIT 6"
);
$galleryImages = $galleryStmt->fetchAll();

$pageTitle = 'Kanak Beauty Parlour | Makeup Artist in Betul';
$pageDesc = 'Discover professional bridal makeup, hairstyling and beauty services at Kanak Beauty Parlour in Betul, Madhya Pradesh.';
$currentPage = 'index';
include 'includes/header.php';
?>
<section class="hero">
    <div class="hero-bg-image" style="background-image:url('https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&amp;fit=crop&amp;w=2000&amp;q=85')"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <p class="hero-eyebrow">Kanak Beauty Parlour · Betul</p>
        <h1>Beauty That Makes <em>You Shine</em></h1>
        <p>Professional makeup and beauty services for your most meaningful moments.</p>
        <div class="hero-actions">
            <a class="btn btn-gold" href="appointment.php">Book an Appointment</a>
            <a class="btn btn-outline-gold" href="services.php">Explore Services</a>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="sub-label">A little about us</span>
            <h2>Beauty, With a Personal Touch</h2>
            <span class="gold-line"></span>
            <p><?= htmlspecialchars($settings['tagline'] ?? 'Beauty That Makes You Shine.', ENT_QUOTES, 'UTF-8') ?> From bridal artistry to everyday beauty care, we make every visit feel special.</p>
        </div>
        <div class="about-layout">
            <div class="about-image-wrap">
                <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&amp;fit=crop&amp;w=900&amp;q=80" alt="Makeup artist preparing a client">
            </div>
            <div class="about-content">
                <span class="sub-label">Meet your artist</span>
                <h2>Care and artistry in every detail</h2>
                <p>Led by <?= htmlspecialchars($settings['owner_name'] ?? 'Kavita Kawadker', ENT_QUOTES, 'UTF-8') ?>, Kanak Beauty Parlour is a welcoming space for professional beauty services in Betul. We listen to your style and occasion, then create a look that feels like you.</p>
                <p>Whether you are preparing for a wedding, celebration, or simply treating yourself, you can expect thoughtful care and attention from consultation to finishing touches.</p>
                <a class="btn btn-dark-luxury" href="about.php">Our Story</a>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-ivory">
    <div class="container">
        <div class="section-header">
            <span class="sub-label">What we offer</span>
            <h2>Services for Every Occasion</h2>
            <span class="gold-line"></span>
            <p>Discover the service that helps you feel your most confident.</p>
        </div>
        <div class="services-grid">
            <?php foreach (array_slice($services, 0, 3) as $service): ?>
                <article class="service-card">
                    <div class="service-card-image">
                        <img src="<?= htmlspecialchars($service['image'] ?: 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=800&q=80', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                        <span class="service-card-category"><?= htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="service-card-body">
                        <h3><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="service-card-footer">
                        <span class="service-price"><?= htmlspecialchars(formatPrice($service['price']), ENT_QUOTES, 'UTF-8') ?></span>
                        <a href="appointment.php?service_id=<?= (int)$service['id'] ?>">Book this service</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (!$services): ?>
            <p class="text-center">Our service list is being updated. Please call us for details.</p>
        <?php endif; ?>
        <p class="text-center mt-4"><a class="btn btn-outline-gold" href="services.php">View All Services</a></p>
    </div>
</section>

<?php if ($galleryImages): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="sub-label">Beauty captured</span>
            <h2>A Glimpse of Our Work</h2>
            <span class="gold-line"></span>
        </div>
        <div class="gallery-grid">
            <?php foreach ($galleryImages as $image): ?>
                <a class="gallery-item" href="gallery.php" aria-label="View <?= htmlspecialchars($image['title'] ?: $image['category'], ENT_QUOTES, 'UTF-8') ?> in our gallery">
                    <img src="<?= htmlspecialchars($image['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image['title'] ?: $image['category'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <div class="gallery-overlay"><h4><?= htmlspecialchars($image['title'] ?: $image['category'], ENT_QUOTES, 'UTF-8') ?></h4></div>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="text-center mt-4"><a class="btn btn-outline-gold" href="gallery.php">Visit the Gallery</a></p>
    </div>
</section>
<?php endif; ?>

<?php if ($reviews): ?>
<section class="testimonials-section section-padding">
    <div class="container">
        <div class="section-header light">
            <span class="sub-label">Kind words</span>
            <h2>Our Clients’ Experiences</h2>
            <span class="gold-line"></span>
        </div>
        <div class="testimonials-grid">
            <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                <article class="testimonial-card">
                    <span class="testimonial-quote-icon" aria-hidden="true">“</span>
                    <div class="testimonial-stars" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('★', max(1, min(5, (int)$review['rating']))) ?></div>
                    <p><?= htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="testimonial-author-info">
                        <span class="name"><?= htmlspecialchars($review['customer_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="role"><?= htmlspecialchars($review['service'] ?? 'Valued client', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-padding">
    <div class="container text-center">
        <div class="section-header">
            <span class="sub-label">We would love to see you</span>
            <h2>Let’s Plan Your Visit</h2>
            <span class="gold-line"></span>
            <p>Tell us what you have in mind and request a time that works for you.</p>
        </div>
        <a class="btn btn-gold btn-lg" href="appointment.php">Book an Appointment</a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
