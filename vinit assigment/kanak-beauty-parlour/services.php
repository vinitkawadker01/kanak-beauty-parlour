<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();
$settings = getSettings($pdo);
$services = getActiveServices($pdo);
$pageTitle = 'Beauty Services | Kanak Beauty Parlour Betul';
$pageDesc = 'Explore bridal makeup, party makeup, hairstyling, skincare and beauty services at Kanak Beauty Parlour in Betul.';
$currentPage = 'services';
include 'includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="hero-content text-center">
            <p class="hero-subtitle">Made for Your Moment</p>
            <h1 class="hero-title">Our Beauty Services</h1>
            <p class="hero-description">Thoughtful, professional care for celebrations and everyday confidence.</p>
        </div>
    </div>
</section>
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="sub-label">Discover your look</span>
            <h2>Services &amp; Treatments</h2>
            <span class="gold-line"></span>
            <p>Prices may vary depending on your requirements. Please contact us for a consultation and quote.</p>
        </div>
        <?php if ($services): ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <article class="service-card">
                        <div class="service-card-image">
                            <?php if (!empty($service['image'])): ?>
                                <img src="<?= htmlspecialchars($service['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <div class="service-image-placeholder" role="img" aria-label="<?= htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8') ?> service"></div>
                            <?php endif; ?>
                            <span class="service-card-category"><?= htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="service-card-body">
                            <h3><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="service-card-footer">
                            <div>
                                <span class="service-price"><?= htmlspecialchars(formatPrice($service['price']), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (!empty($service['duration'])): ?>
                                    <span class="service-duration"><?= htmlspecialchars($service['duration'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>
                            <a class="btn btn-sm btn-gold" href="appointment.php?service_id=<?= (int)$service['id'] ?>">Book</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">Our services are being updated. Call <?= htmlspecialchars($settings['phone'] ?? '8839131870', ENT_QUOTES, 'UTF-8') ?> to discuss your requirements.</p>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
