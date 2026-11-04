<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();

$settings = getSettings($pdo);
$whatsapp = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '918839131870');
$selectedCategory = isset($_GET['cat']) && is_string($_GET['cat']) ? trim($_GET['cat']) : 'all';
$categorySlug = static function (string $value): string {
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($value)));
    return trim($slug ?? '', '-');
};

// Fetch gallery images
$stmt = $pdo->prepare("SELECT id, title, image AS image_path, category FROM gallery WHERE is_active=1 ORDER BY sort_order ASC, created_at DESC");
$stmt->execute();
$images = $stmt->fetchAll();

// Get unique categories
$catStmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE is_active=1 ORDER BY category");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
$filterCategories = $categories ?: ['Bridal', 'Party', 'Engagement', 'Hairstyling', 'Traditional'];

$pageTitle = "Makeup Gallery | Kanak Beauty Parlour Betul";
$pageDesc = "Browse our bridal makeup, party makeup, hairstyling and beauty portfolio.";
$currentPage = 'gallery';
include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content text-center">
            <p class="hero-subtitle">Beauty Captured</p>
            <h1 class="hero-title">Our Portfolio</h1>
            <div class="gold-divider">
                <span class="line"></span>
                <span class="diamond">◆</span>
                <span class="line"></span>
            </div>
            <p class="hero-description">Every look tells a story — explore the artistry of Kanak Beauty Parlour</p>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="gallery-section section-padding">
    <div class="container">

        <!-- Category Filters -->
        <div class="gallery-filters" role="tablist" aria-label="Gallery categories">
            <button class="filter-btn<?= $selectedCategory === 'all' ? ' active' : '' ?>" data-filter="all" role="tab" aria-selected="<?= $selectedCategory === 'all' ? 'true' : 'false' ?>">All</button>
            <?php foreach ($filterCategories as $filterCategory): ?>
                <?php $filterSlug = $categorySlug((string)$filterCategory); ?>
                <button class="filter-btn<?= $selectedCategory === $filterSlug ? ' active' : '' ?>" data-filter="<?= htmlspecialchars($filterSlug, ENT_QUOTES, 'UTF-8') ?>" role="tab" aria-selected="<?= $selectedCategory === $filterSlug ? 'true' : 'false' ?>"><?= htmlspecialchars((string)$filterCategory, ENT_QUOTES, 'UTF-8') ?></button>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($images)): ?>
        <!-- Image Grid -->
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($images as $img): ?>
            <?php
                $catSlug = $categorySlug((string)$img['category']);
                $imgSrc  = !empty($img['image_path']) ? htmlspecialchars($img['image_path']) : 'assets/images/placeholder-gallery.jpg';
                $title   = htmlspecialchars($img['title'] ?? 'Beauty Work');
                $cat     = htmlspecialchars($img['category'] ?? 'General');
            ?>
            <div class="gallery-item" data-category="<?= $catSlug ?>">
                <div class="gallery-card">
                    <div class="gallery-img-wrap">
                        <img
                            src="<?= $imgSrc ?>"
                            alt="<?= $title ?> – Kanak Beauty Parlour"
                            class="gallery-img"
                            loading="lazy"
                            data-lightbox-src="<?= $imgSrc ?>"
                            data-lightbox-title="<?= $title ?>"
                            data-lightbox-cat="<?= $cat ?>"
                        >
                        <div class="gallery-overlay">
                            <div class="overlay-content">
                                <span class="overlay-icon">🔍</span>
                                <h3 class="overlay-title"><?= $title ?></h3>
                                <span class="overlay-cat"><?= $cat ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- No results message (shown via JS) -->
        <div class="gallery-empty" id="galleryEmpty" style="display:none;">
            <div class="empty-content">
                <span class="empty-icon">📸</span>
                <h3>No images in this category yet</h3>
                <p>Check back soon for more beautiful work!</p>
            </div>
        </div>

        <?php else: ?>
        <!-- Placeholder Grid when no images in DB -->
        <div class="gallery-placeholder-grid" id="galleryGrid">
            <?php
            $placeholderCategories = ['Bridal', 'Party', 'Engagement', 'Hairstyling', 'Traditional', 'Bridal', 'Party', 'Engagement', 'Traditional'];
            foreach ($placeholderCategories as $i => $pCat):
                $pSlug = strtolower($pCat);
            ?>
            <div class="gallery-item placeholder-item" data-category="<?= $categorySlug($pCat) ?>">
                <div class="gallery-card placeholder-card">
                    <div class="gallery-img-wrap">
                        <div class="placeholder-box">
                            <span class="placeholder-icon">📸</span>
                            <span class="placeholder-label"><?= $pCat ?></span>
                        </div>
                        <div class="gallery-overlay">
                            <div class="overlay-content">
                                <span class="overlay-cat"><?= $pCat ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="coming-soon-banner text-center">
            <div class="cs-inner">
                <div class="cs-icon">✨</div>
                <h2 class="cs-title">Portfolio Coming Soon</h2>
                <p class="cs-desc">We are curating our finest work to showcase here.<br>Visit us on Instagram for the latest looks!</p>
                <a href="https://www.instagram.com/kavita_kawadker_makeup_artist" target="_blank" rel="noopener" class="btn btn-gold">
                    <span>📸</span> View on Instagram
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lightbox Modal -->
        <div class="lightbox-overlay" id="lightboxOverlay" role="dialog" aria-modal="true" aria-label="Image viewer" style="display:none;">
            <button class="lightbox-close" id="lightboxClose" aria-label="Close image viewer">✕</button>
            <button class="lightbox-prev" id="lightboxPrev" aria-label="Previous image">&#8249;</button>
            <button class="lightbox-next" id="lightboxNext" aria-label="Next image">&#8250;</button>
            <div class="lightbox-inner">
                <div class="lightbox-loader" id="lightboxLoader">
                    <div class="spinner"></div>
                </div>
                <img src="" alt="" class="lightbox-img" id="lightboxImg">
                <div class="lightbox-caption" id="lightboxCaption">
                    <h3 id="lightboxTitle"></h3>
                    <span id="lightboxCat"></span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Book Now CTA -->
<section class="gallery-cta section-padding-sm">
    <div class="container text-center">
        <div class="cta-inner">
            <h2>Love What You See?</h2>
            <p>Book your appointment and let us create your perfect look</p>
            <div class="cta-buttons">
                <a href="appointment.php" class="btn btn-gold">Book Appointment</a>
                <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Kanak%20Beauty%20Parlour!%20I%20saw%20your%20gallery%20and%20would%20like%20to%20book%20an%20appointment." target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp Us
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* ===== Page Hero ===== */
.page-hero {
    position: relative;
    background: linear-gradient(135deg, #111111 0%, #292929 50%, #111111 100%);
    padding: 120px 0 80px;
    overflow: hidden;
    text-align: center;
}
.page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url('assets/images/gallery-hero-bg.jpg') center/cover no-repeat;
    opacity: 0.15;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(17,17,17,0.6) 0%, rgba(17,17,17,0.85) 100%);
}
.hero-content { position: relative; z-index: 2; }
.hero-subtitle {
    font-size: 0.9rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: #D4AF75;
    margin-bottom: 12px;
    font-weight: 400;
}
.hero-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    color: #FAF7F2;
    font-weight: 700;
    margin: 0 0 20px;
    font-family: 'Playfair Display', Georgia, serif;
}
.gold-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 20px;
}
.gold-divider .line {
    width: 80px;
    height: 1px;
    background: #D4AF75;
}
.gold-divider .diamond { color: #D4AF75; font-size: 0.7rem; }
.hero-description { color: rgba(250,247,242,0.75); font-size: 1.05rem; }

/* ===== Gallery Section ===== */
.gallery-section { background: #FAF7F2; }
.section-padding { padding: 80px 0; }
.section-padding-sm { padding: 60px 0; }

/* ===== Filters ===== */
.gallery-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-bottom: 50px;
}
.filter-btn {
    padding: 10px 24px;
    border: 1.5px solid #D4AF75;
    background: transparent;
    color: #292929;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    cursor: pointer;
    border-radius: 30px;
    transition: all 0.3s ease;
    font-family: inherit;
}
.filter-btn:hover,
.filter-btn.active {
    background: #D4AF75;
    color: #111111;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(212,175,117,0.3);
}

/* ===== Gallery Grid ===== */
.gallery-grid,
.gallery-placeholder-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}
@media (max-width: 900px) {
    .gallery-grid,
    .gallery-placeholder-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 540px) {
    .gallery-grid,
    .gallery-placeholder-grid { grid-template-columns: 1fr; }
}

.gallery-item {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.gallery-item.hidden {
    display: none;
}

.gallery-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    background: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}
.gallery-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); }

.gallery-img-wrap { position: relative; overflow: hidden; aspect-ratio: 3/4; }
.gallery-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.gallery-card:hover .gallery-img { transform: scale(1.06); }

/* Overlay */
.gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(17,17,17,0.85) 0%, rgba(17,17,17,0.2) 60%, transparent 100%);
    display: flex;
    align-items: flex-end;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.gallery-card:hover .gallery-overlay { opacity: 1; }
.overlay-content { text-align: left; }
.overlay-icon { font-size: 1.4rem; display: block; margin-bottom: 6px; }
.overlay-title { color: #FAF7F2; font-size: 1rem; font-weight: 600; margin: 0 0 4px; font-family: 'Playfair Display', Georgia, serif; }
.overlay-cat {
    display: inline-block;
    background: #D4AF75;
    color: #111;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 2px 10px;
    border-radius: 20px;
}

/* Placeholder items */
.placeholder-card { cursor: default; }
.placeholder-box {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f0ebe3 0%, #e8e0d5 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.placeholder-icon { font-size: 2.5rem; opacity: 0.5; }
.placeholder-label { font-size: 0.85rem; color: #aaa; font-weight: 500; letter-spacing: 0.08em; text-transform: uppercase; }

/* Coming soon banner */
.coming-soon-banner { margin-top: 20px; }
.cs-inner {
    display: inline-block;
    background: #fff;
    border: 1.5px solid #D4AF75;
    border-radius: 12px;
    padding: 50px 60px;
    max-width: 480px;
}
.cs-icon { font-size: 2.5rem; margin-bottom: 16px; }
.cs-title { font-size: 1.8rem; color: #111; font-family: 'Playfair Display', Georgia, serif; margin-bottom: 12px; }
.cs-desc { color: #666; line-height: 1.6; margin-bottom: 24px; }

/* Gallery empty */
.gallery-empty { text-align: center; padding: 60px 20px; }
.empty-content .empty-icon { font-size: 3rem; display: block; margin-bottom: 16px; }
.empty-content h3 { font-size: 1.4rem; color: #292929; margin-bottom: 8px; }
.empty-content p { color: #888; }

/* ===== Lightbox ===== */
.lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.lightbox-inner {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.lightbox-img {
    max-width: 90vw;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 4px;
    display: block;
    transition: opacity 0.3s ease;
}
.lightbox-caption {
    text-align: center;
    margin-top: 16px;
    color: #FAF7F2;
}
.lightbox-caption h3 {
    font-size: 1.1rem;
    font-family: 'Playfair Display', Georgia, serif;
    margin-bottom: 6px;
}
.lightbox-caption span {
    display: inline-block;
    background: #D4AF75;
    color: #111;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 2px 12px;
    border-radius: 20px;
}
.lightbox-close,
.lightbox-prev,
.lightbox-next {
    position: absolute;
    background: rgba(212,175,117,0.15);
    border: 1px solid #D4AF75;
    color: #D4AF75;
    cursor: pointer;
    border-radius: 50%;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    transition: all 0.2s ease;
    z-index: 10000;
}
.lightbox-close { top: -50px; right: 0; }
.lightbox-prev { left: -60px; top: 50%; transform: translateY(-50%); }
.lightbox-next { right: -60px; top: 50%; transform: translateY(-50%); }
.lightbox-close:hover,
.lightbox-prev:hover,
.lightbox-next:hover { background: #D4AF75; color: #111; }
.lightbox-loader {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.spinner {
    width: 40px; height: 40px;
    border: 3px solid rgba(212,175,117,0.3);
    border-top-color: #D4AF75;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
    .lightbox-prev { left: 10px; bottom: 20px; top: auto; transform: none; }
    .lightbox-next { right: 10px; bottom: 20px; top: auto; transform: none; }
    .lightbox-close { top: 10px; right: 10px; }
}

/* ===== Gallery CTA ===== */
.gallery-cta { background: #111111; }
.gallery-cta .cta-inner { max-width: 600px; margin: 0 auto; }
.gallery-cta h2 { color: #FAF7F2; font-size: 2rem; font-family: 'Playfair Display', Georgia, serif; margin-bottom: 12px; }
.gallery-cta p { color: rgba(250,247,242,0.7); margin-bottom: 30px; }
.cta-buttons { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    border-radius: 4px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.05em;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}
.btn-gold { background: #D4AF75; color: #111111; }
.btn-gold:hover { background: #c9a05e; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,175,117,0.4); }
.btn-whatsapp { background: #25D366; color: #fff; }
.btn-whatsapp:hover { background: #1ebe5a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.3); }

.text-center { text-align: center; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
</style>

<script>
(function() {
    'use strict';

    // ===== Category Filtering =====
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    const galleryEmpty = document.getElementById('galleryEmpty');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Update active button
            filterBtns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            let visibleCount = 0;
            galleryItems.forEach(item => {
                const cat = item.dataset.category;
                const show = (filter === 'all' || cat === filter);
                if (show) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (galleryEmpty) {
                galleryEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    });
    const selectedFilter = document.querySelector('.filter-btn.active');
    if (selectedFilter && selectedFilter.dataset.filter !== 'all') {
        selectedFilter.click();
    }

    // ===== Lightbox =====
    const overlay    = document.getElementById('lightboxOverlay');
    const lbImg      = document.getElementById('lightboxImg');
    const lbTitle    = document.getElementById('lightboxTitle');
    const lbCat      = document.getElementById('lightboxCat');
    const lbLoader   = document.getElementById('lightboxLoader');
    const lbClose    = document.getElementById('lightboxClose');
    const lbPrev     = document.getElementById('lightboxPrev');
    const lbNext     = document.getElementById('lightboxNext');

    if (!overlay) return;

    let currentIndex = 0;
    let activeImages = [];

    function getActiveImages() {
        return Array.from(document.querySelectorAll('.gallery-item:not(.hidden) .gallery-img[data-lightbox-src]'));
    }

    function openLightbox(index) {
        activeImages = getActiveImages();
        if (!activeImages.length) return;
        currentIndex = index;
        loadImage(currentIndex);
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        lbClose.focus();
    }

    function closeLightbox() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    function loadImage(index) {
        const imgEl = activeImages[index];
        const src   = imgEl.dataset.lightboxSrc;
        const title = imgEl.dataset.lightboxTitle || '';
        const cat   = imgEl.dataset.lightboxCat   || '';

        lbLoader.style.display = 'flex';
        lbImg.style.opacity = '0';

        const tempImg = new Image();
        tempImg.onload = function() {
            lbImg.src = src;
            lbImg.alt = title;
            lbTitle.textContent = title;
            lbCat.textContent   = cat;
            lbLoader.style.display = 'none';
            lbImg.style.opacity = '1';
        };
        tempImg.onerror = function() {
            lbLoader.style.display = 'none';
            lbImg.src = src;
            lbImg.style.opacity = '1';
        };
        tempImg.src = src;

        // Hide nav if only one image
        const showNav = activeImages.length > 1;
        lbPrev.style.display = showNav ? 'flex' : 'none';
        lbNext.style.display = showNav ? 'flex' : 'none';
    }

    function prevImage() {
        currentIndex = (currentIndex - 1 + activeImages.length) % activeImages.length;
        loadImage(currentIndex);
    }

    function nextImage() {
        currentIndex = (currentIndex + 1) % activeImages.length;
        loadImage(currentIndex);
    }

    // Click on gallery images
    document.querySelectorAll('.gallery-img[data-lightbox-src]').forEach((img, idx) => {
        img.addEventListener('click', function() {
            const visibleImgs = getActiveImages();
            const visibleIdx = visibleImgs.indexOf(this);
            openLightbox(visibleIdx >= 0 ? visibleIdx : 0);
        });
        img.style.cursor = 'pointer';
    });

    lbClose.addEventListener('click', closeLightbox);
    lbPrev.addEventListener('click', prevImage);
    lbNext.addEventListener('click', nextImage);

    // Click outside to close
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (overlay.style.display !== 'flex') return;
        switch(e.key) {
            case 'Escape':    closeLightbox(); break;
            case 'ArrowLeft': prevImage();     break;
            case 'ArrowRight':nextImage();     break;
        }
    });

    // Touch swipe support
    let touchStartX = 0;
    overlay.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
    overlay.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            diff > 0 ? nextImage() : prevImage();
        }
    }, { passive: true });

})();
</script>

<?php include 'includes/footer.php'; ?>
