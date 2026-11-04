<?php
$footerPhone = $settings['phone'] ?? '8839131870';
$footerWhatsapp = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '918839131870');
$footerEmail = $settings['email'] ?? 'info@kanakbeauty.com';
$footerAddress = $settings['address'] ?? 'Chandrashekhar Ward, Sadar, Betul, Madhya Pradesh, Near Sai Mandir';
$footerInstagram = $settings['instagram'] ?? 'https://www.instagram.com/kavita_kawadker_makeup_artist';
?>
</main>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="logo-text">Kanak Beauty Parlour</span>
                <p>Beauty That Makes You Shine. Professional makeup and beauty services, thoughtfully tailored for every occasion.</p>
                <div class="footer-social">
                    <a class="social-btn" href="<?= htmlspecialchars($footerInstagram, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Visit our Instagram">
                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                    </a>
                    <a class="social-btn" href="https://wa.me/<?= htmlspecialchars($footerWhatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="services.php">Our Services</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="appointment.php">Book Appointment</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <div class="footer-contact-item">
                    <i class="icon fa-solid fa-location-dot" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($footerAddress, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="footer-contact-item">
                    <i class="icon fa-solid fa-phone" aria-hidden="true"></i>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $footerPhone), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerPhone, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="footer-contact-item">
                    <i class="icon fa-solid fa-envelope" aria-hidden="true"></i>
                    <a href="mailto:<?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Opening Hours</h4>
                <table class="hours-table">
                    <tbody>
                    <tr><td>Monday – Sunday</td><td><?= htmlspecialchars($settings['business_hours'] ?? '10:00 AM – 8:00 PM', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Kanak Beauty Parlour. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
            </div>
        </div>
    </div>
</footer>
<a class="whatsapp-float" href="https://wa.me/<?= htmlspecialchars($footerWhatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with Kanak Beauty Parlour on WhatsApp">
    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
</a>
<button class="back-to-top" id="backToTop" type="button" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>
<script src="assets/js/main.js" defer></script>
</body>
</html>
