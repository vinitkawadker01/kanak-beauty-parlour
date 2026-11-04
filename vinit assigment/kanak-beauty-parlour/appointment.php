<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();

$services = getActiveServices($pdo);
$settings = getSettings($pdo);
$phone = $settings['phone'] ?? '8839131870';
$phoneDigits = preg_replace('/\D+/', '', $phone);
$whatsapp = preg_replace('/\D+/', '', $settings['whatsapp'] ?? '918839131870');

// Pre-select service if passed via URL
$preSelectedService = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

// If booking_id in session (just submitted), show confirmation
$bookingConfirmed = false;
$bookingData = null;
if (isset($_SESSION['booking_confirmed'])) {
    $bookingConfirmed = true;
    $bookingData = $_SESSION['booking_confirmed'];
    unset($_SESSION['booking_confirmed']);
}

$csrfToken = generateCsrfToken();

$pageTitle = "Book Appointment | Kanak Beauty Parlour Betul";
$pageDesc  = "Book your beauty appointment with Kavita Kawadker at Kanak Beauty Parlour, Betul.";
$currentPage = 'appointment';
include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero appt-hero">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content text-center">
            <p class="hero-subtitle">Reserve Your Spot</p>
            <h1 class="hero-title">Book an Appointment</h1>
            <div class="gold-divider">
                <span class="line"></span>
                <span class="diamond">◆</span>
                <span class="line"></span>
            </div>
            <p class="hero-description">Step into luxury — let us transform your look for your special moment</p>
        </div>
    </div>
</section>

<section class="appointment-section section-padding">
    <div class="container">

        <?php if ($bookingConfirmed && $bookingData): ?>
        <!-- ===== BOOKING CONFIRMATION ===== -->
        <div class="confirmation-wrap text-center">
            <div class="confirmation-card">
                <div class="confirm-icon-wrap">
                    <div class="confirm-icon">✓</div>
                </div>
                <h2 class="confirm-title">Appointment Booked!</h2>
                <p class="confirm-subtitle">Your request has been received successfully</p>

                <div class="confirm-details">
                    <div class="confirm-row">
                        <span class="confirm-label">Booking ID</span>
                        <span class="confirm-value booking-id"><?= htmlspecialchars($bookingData['booking_id']) ?></span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">Customer</span>
                        <span class="confirm-value"><?= htmlspecialchars($bookingData['customer_name']) ?></span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">Service</span>
                        <span class="confirm-value"><?= htmlspecialchars($bookingData['service_name'] ?? 'Beauty Service') ?></span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">Date &amp; Time</span>
                        <span class="confirm-value">
                            <?= date('D, d M Y', strtotime($bookingData['appt_date'])) ?>
                            at <?= date('h:i A', strtotime($bookingData['appt_time'])) ?>
                        </span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">Status</span>
                        <span class="confirm-value status-badge status-pending">⏳ Pending Confirmation</span>
                    </div>
                    <div class="confirm-row">
                        <span class="confirm-label">Payment</span>
                        <span class="confirm-value">
                            <?= ($bookingData['payment_status'] ?? '') === 'successful' ? 'Paid online' : htmlspecialchars(ucfirst($bookingData['payment_method'] ?? 'cash'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>

                <div class="confirm-note">
                    <p>🕐 Our team will confirm your appointment within <strong>2–3 hours</strong>.</p>
                    <p>You will receive a call or message at <strong><?= htmlspecialchars($bookingData['mobile']) ?></strong>.</p>
                </div>

                <div class="confirm-actions">
                    <?php
                    $waMsg = urlencode("Hi Kanak Beauty Parlour! My booking ID is " . ($bookingData['booking_id'] ?? '') . ". I'd like to confirm my appointment.");
                    ?>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>?text=<?= $waMsg ?>"
                       target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Chat on WhatsApp for Quick Confirmation
                    </a>
                    <a href="appointment.php" class="btn btn-outline-gold">📅 Book Another Appointment</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ===== BOOKING FORM ===== -->
        <div class="appt-layout">

            <!-- Left: Form -->
            <div class="appt-form-col">
                <div class="form-card">
                    <div class="form-card-header">
                        <h2>Request an Appointment</h2>
                        <p>Fill in the details below and we'll confirm within 2–3 hours</p>
                    </div>

                    <!-- Alert container -->
                    <div id="formAlert" class="form-alert" style="display:none;" role="alert" aria-live="polite"></div>

                    <form id="appointmentForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="form-row-2">
                            <!-- Customer Name -->
                            <div class="form-group">
                                <label for="customer_name" class="form-label">
                                    Full Name <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="customer_name"
                                    name="customer_name"
                                    class="form-input"
                                    placeholder="Your full name"
                                    required
                                    minlength="2"
                                    maxlength="100"
                                    autocomplete="name"
                                    aria-required="true"
                                >
                                <span class="field-error" id="err_name"></span>
                            </div>

                            <!-- Mobile -->
                            <div class="form-group">
                                <label for="mobile" class="form-label">
                                    Mobile Number <span class="required" aria-hidden="true">*</span>
                                </label>
                                <div class="input-with-prefix">
                                    <span class="input-prefix">+91</span>
                                    <input
                                        type="tel"
                                        id="mobile"
                                        name="mobile"
                                        class="form-input has-prefix"
                                        placeholder="10-digit mobile"
                                        required
                                        pattern="[6-9][0-9]{9}"
                                        maxlength="10"
                                        autocomplete="tel"
                                        aria-required="true"
                                        inputmode="numeric"
                                    >
                                </div>
                                <span class="field-error" id="err_mobile"></span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Email Address <span class="optional">(optional)</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="your@email.com"
                                autocomplete="email"
                            >
                            <span class="field-error" id="err_email"></span>
                        </div>

                        <!-- Service Selection -->
                        <div class="form-group">
                            <label for="service_id" class="form-label">
                                Select Service <span class="required" aria-hidden="true">*</span>
                            </label>
                            <select id="service_id" name="service_id" class="form-select" required aria-required="true">
                                <option value="">— Choose a service —</option>
                                <?php foreach ($services as $svc): ?>
                                <option
                                    value="<?= (int)$svc['id'] ?>"
                                    data-price="<?= (float)($svc['price'] ?? 0) ?>"
                                    <?= ($preSelectedService === (int)$svc['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($svc['name']) ?>
                                    <?php if (!empty($svc['price']) && $svc['price'] > 0): ?>
                                        — ₹<?= number_format($svc['price'], 0) ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="field-error" id="err_service"></span>
                        </div>

                        <div class="form-row-2">
                            <!-- Appointment Date -->
                            <div class="form-group">
                                <label for="appt_date" class="form-label">
                                    Appointment Date <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="appt_date"
                                    name="appt_date"
                                    class="form-input"
                                    required
                                    aria-required="true"
                                    min="<?= date('Y-m-d') ?>"
                                    max="<?= date('Y-m-d', strtotime('+90 days')) ?>"
                                >
                                <span class="field-error" id="err_date"></span>
                            </div>

                            <!-- Preferred Time -->
                            <div class="form-group">
                                <label for="appt_time" class="form-label">
                                    Preferred Time <span class="required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="time"
                                    id="appt_time"
                                    name="appt_time"
                                    class="form-input"
                                    required
                                    aria-required="true"
                                    min="10:00"
                                    max="20:00"
                                >
                                <small class="field-hint">Between 10:00 AM – 8:00 PM</small>
                                <span class="field-error" id="err_time"></span>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="form-group">
                            <label for="instructions" class="form-label">
                                Special Instructions <span class="optional">(optional)</span>
                            </label>
                            <textarea
                                id="instructions"
                                name="instructions"
                                class="form-textarea"
                                placeholder="Any specific requirements, allergies, reference images, etc."
                                rows="4"
                                maxlength="500"
                            ></textarea>
                            <div class="char-count"><span id="instrCount">0</span>/500</div>
                            <span class="field-error" id="err_instructions"></span>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group">
                            <label class="form-label">
                                Payment Method <span class="required" aria-hidden="true">*</span>
                            </label>
                            <div class="payment-options" role="radiogroup" aria-label="Payment method">
                                <label class="payment-option" for="pay_cash">
                                    <input type="radio" id="pay_cash" name="payment_method" value="cash" checked class="payment-radio">
                                    <span class="payment-option-inner">
                                        <span class="payment-icon">💵</span>
                                        <span class="payment-info">
                                            <span class="payment-name">Cash at Parlour</span>
                                            <span class="payment-desc">Pay when you visit</span>
                                        </span>
                                    </span>
                                </label>
                                <label class="payment-option" for="pay_upi">
                                    <input type="radio" id="pay_upi" name="payment_method" value="upi" class="payment-radio">
                                    <span class="payment-option-inner">
                                        <span class="payment-icon">📱</span>
                                        <span class="payment-info">
                                            <span class="payment-name">UPI</span>
                                            <span class="payment-desc">GPay, PhonePe, Paytm</span>
                                        </span>
                                    </span>
                                </label>
                                <label class="payment-option" for="pay_razorpay">
                                    <input type="radio" id="pay_razorpay" name="payment_method" value="razorpay" class="payment-radio">
                                    <span class="payment-option-inner">
                                        <span class="payment-icon">💳</span>
                                        <span class="payment-info">
                                            <span class="payment-name">Online</span>
                                            <span class="payment-desc">Card / Net Banking (Razorpay)</span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <span class="field-error" id="err_payment"></span>
                        </div>

                        <!-- Submit -->
                        <div class="form-submit">
                            <button type="submit" class="btn btn-gold btn-submit" id="submitBtn">
                                <span class="btn-text">📅 Request Appointment</span>
                                <span class="btn-loading" style="display:none;">
                                    <span class="btn-spinner"></span> Processing…
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- WhatsApp Alternative -->
                    <div class="wa-alternative">
                        <div class="divider-text"><span>OR</span></div>
                        <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Kanak%20Beauty%20Parlour!%20I%20would%20like%20to%20book%20an%20appointment."
                           target="_blank" rel="noopener" class="btn btn-whatsapp btn-full">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Book via WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Info Sidebar -->
            <aside class="appt-info-col" aria-label="Parlour information">
                <!-- Hours Card -->
                <div class="info-card">
                    <div class="info-card-icon">🕐</div>
                    <h3 class="info-card-title">Business Hours</h3>
                    <ul class="hours-list">
                        <li>
                            <span>Monday – Sunday</span>
                            <span class="hours-time"><?= htmlspecialchars($settings['business_hours'] ?? '10:00 AM – 8:00 PM', ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    </ul>
                    <div class="open-badge">🟢 Open Daily</div>
                </div>

                <!-- Address Card -->
                <div class="info-card">
                    <div class="info-card-icon">📍</div>
                    <h3 class="info-card-title">Our Location</h3>
                    <address class="info-address">
                        Kanak Beauty Parlour<br>
                        <?= htmlspecialchars($settings['address'] ?? 'Chandrashekhar Ward, Sadar, Betul, Madhya Pradesh, Near Sai Mandir', ENT_QUOTES, 'UTF-8') ?>
                    </address>
                    <a href="https://maps.google.com/?q=Kanak+Beauty+Parlour+Betul+MP"
                       target="_blank" rel="noopener" class="info-link">
                        📌 View on Google Maps
                    </a>
                </div>

                <!-- Contact Card -->
                <div class="info-card">
                    <div class="info-card-icon">📞</div>
                    <h3 class="info-card-title">Contact Us</h3>
                    <a href="tel:<?= htmlspecialchars($phoneDigits, ENT_QUOTES, 'UTF-8') ?>" class="info-phone"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a>
                    <a href="https://wa.me/<?= htmlspecialchars($whatsapp, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="info-wa-mini">
                        💬 WhatsApp
                    </a>
                </div>

                <!-- Confirmation Note -->
                <div class="info-card info-note-card">
                    <div class="note-icon">ℹ️</div>
                    <h3 class="info-card-title">Please Note</h3>
                    <ul class="note-list">
                        <li>Appointments are confirmed by our team</li>
                        <li>You will be contacted within <strong>2–3 hours</strong></li>
                        <li>For urgent bookings, WhatsApp us directly</li>
                        <li>Please arrive 5 minutes early</li>
                    </ul>
                </div>
            </aside>
        </div>
        <?php endif; ?>

    </div>
</section>

<style>
/* ===== Page Hero ===== */
.page-hero {
    position: relative;
    background: linear-gradient(135deg, #111111 0%, #292929 60%, #111111 100%);
    padding: 120px 0 80px;
    text-align: center;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url('assets/images/appointment-hero-bg.jpg') center/cover no-repeat;
    opacity: 0.12;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(17,17,17,0.6) 0%, rgba(17,17,17,0.85) 100%);
}
.hero-content { position: relative; z-index: 2; }
.hero-subtitle { font-size: 0.9rem; letter-spacing: 0.3em; text-transform: uppercase; color: #D4AF75; margin-bottom: 12px; }
.hero-title { font-size: clamp(2rem, 5vw, 3.2rem); color: #FAF7F2; font-family: 'Playfair Display', Georgia, serif; margin: 0 0 20px; }
.gold-divider { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px; }
.gold-divider .line { width: 80px; height: 1px; background: #D4AF75; }
.gold-divider .diamond { color: #D4AF75; font-size: 0.7rem; }
.hero-description { color: rgba(250,247,242,0.75); }

/* ===== Appointment Layout ===== */
.appointment-section { background: #FAF7F2; }
.section-padding { padding: 80px 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.text-center { text-align: center; }

.appt-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 32px;
    align-items: start;
}
@media (max-width: 900px) {
    .appt-layout { grid-template-columns: 1fr; }
    .appt-info-col { order: 1; }
    .appt-form-col { order: 2; }
}

/* ===== Form Card ===== */
.form-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.08);
    overflow: hidden;
}
.form-card-header {
    background: linear-gradient(135deg, #111111, #292929);
    padding: 32px 36px;
    border-bottom: 3px solid #D4AF75;
}
.form-card-header h2 {
    color: #FAF7F2;
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.6rem;
    margin: 0 0 8px;
}
.form-card-header p { color: rgba(250,247,242,0.7); font-size: 0.95rem; margin: 0; }
form { padding: 32px 36px; }

/* ===== Form Elements ===== */
.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 600px) { .form-row-2 { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 22px; }
.form-label {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #292929;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.required { color: #D4AF75; }
.optional { font-weight: 400; color: #999; text-transform: none; letter-spacing: 0; font-size: 0.82rem; }

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e0d8cc;
    border-radius: 6px;
    font-size: 0.95rem;
    color: #292929;
    background: #fdfaf5;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-family: inherit;
    box-sizing: border-box;
}
.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #D4AF75;
    box-shadow: 0 0 0 3px rgba(212,175,117,0.15);
    background: #fff;
}
.form-input.error,
.form-select.error,
.form-textarea.error {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231,76,60,0.1);
}
.form-input.valid { border-color: #27ae60; }

.input-with-prefix { display: flex; align-items: center; }
.input-prefix {
    padding: 12px 12px;
    background: #f0ebe3;
    border: 1.5px solid #e0d8cc;
    border-right: none;
    border-radius: 6px 0 0 6px;
    font-size: 0.9rem;
    color: #666;
    white-space: nowrap;
}
.form-input.has-prefix { border-radius: 0 6px 6px 0; }

.form-textarea { resize: vertical; min-height: 110px; }
.char-count { text-align: right; font-size: 0.78rem; color: #999; margin-top: 4px; }
.field-hint { display: block; font-size: 0.78rem; color: #999; margin-top: 4px; }
.field-error { display: block; font-size: 0.8rem; color: #e74c3c; margin-top: 4px; min-height: 18px; }

/* ===== Payment Options ===== */
.payment-options { display: flex; flex-direction: column; gap: 10px; }
.payment-option { cursor: pointer; }
.payment-radio { position: absolute; opacity: 0; width: 0; height: 0; }
.payment-option-inner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border: 1.5px solid #e0d8cc;
    border-radius: 8px;
    background: #fdfaf5;
    transition: all 0.2s ease;
}
.payment-radio:checked + .payment-option-inner {
    border-color: #D4AF75;
    background: rgba(212,175,117,0.08);
    box-shadow: 0 0 0 3px rgba(212,175,117,0.15);
}
.payment-option:hover .payment-option-inner { border-color: #D4AF75; }
.payment-icon { font-size: 1.5rem; }
.payment-name { display: block; font-weight: 600; font-size: 0.95rem; color: #292929; }
.payment-desc { display: block; font-size: 0.8rem; color: #888; }

/* ===== Submit ===== */
.form-submit { margin-top: 8px; }
.btn-submit { width: 100%; justify-content: center; padding: 16px 32px; font-size: 1rem; }
.btn-spinner {
    display: inline-block;
    width: 16px; height: 16px;
    border: 2px solid rgba(17,17,17,0.3);
    border-top-color: #111;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* WhatsApp Alternative */
.wa-alternative { padding: 0 36px 32px; }
.divider-text {
    display: flex;
    align-items: center;
    text-align: center;
    color: #bbb;
    font-size: 0.85rem;
    margin-bottom: 16px;
}
.divider-text::before,
.divider-text::after { content: ''; flex: 1; border-bottom: 1px solid #e0d8cc; }
.divider-text span { padding: 0 12px; }
.btn-full { width: 100%; justify-content: center; }

/* ===== Form Alert ===== */
.form-alert {
    margin: 0 36px 20px;
    padding: 14px 18px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
}
.form-alert.alert-error { background: #fdf0f0; border: 1px solid #e74c3c; color: #c0392b; }
.form-alert.alert-success { background: #f0fdf4; border: 1px solid #27ae60; color: #1e8449; }

/* ===== Info Sidebar ===== */
.info-card {
    background: #fff;
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    margin-bottom: 20px;
    border-top: 3px solid #D4AF75;
}
.info-card-icon { font-size: 1.8rem; margin-bottom: 10px; }
.info-card-title { font-size: 1rem; font-weight: 700; color: #111; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.06em; }
.hours-list { list-style: none; padding: 0; margin: 0 0 14px; }
.hours-list li { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0ebe3; font-size: 0.9rem; color: #555; }
.hours-time { font-weight: 600; color: #292929; }
.open-badge { display: inline-block; background: #f0fdf4; border: 1px solid #27ae60; color: #1e8449; font-size: 0.8rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
.info-address { font-style: normal; color: #555; font-size: 0.9rem; line-height: 1.6; margin-bottom: 12px; }
.info-link { color: #D4AF75; font-size: 0.88rem; font-weight: 600; text-decoration: none; }
.info-link:hover { text-decoration: underline; }
.info-phone { display: block; color: #111; font-size: 1.2rem; font-weight: 700; text-decoration: none; margin-bottom: 10px; }
.info-phone:hover { color: #D4AF75; }
.info-wa-mini {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #25D366;
    color: #fff;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 8px 18px;
    border-radius: 20px;
    text-decoration: none;
}
.info-wa-mini:hover { background: #1ebe5a; }
.info-note-card { border-top-color: #D9A6A0; }
.note-icon { font-size: 1.5rem; margin-bottom: 10px; }
.note-list { list-style: none; padding: 0; margin: 0; }
.note-list li { font-size: 0.88rem; color: #555; padding: 6px 0; padding-left: 18px; position: relative; }
.note-list li::before { content: '✓'; position: absolute; left: 0; color: #D4AF75; font-weight: 700; }

/* ===== Confirmation Card ===== */
.confirmation-wrap { max-width: 600px; margin: 0 auto; padding: 20px 0; }
.confirmation-card {
    background: #fff;
    border-radius: 16px;
    padding: 50px 40px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.12);
    text-align: center;
}
.confirm-icon-wrap { margin-bottom: 20px; }
.confirm-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #D4AF75, #c9a05e);
    border-radius: 50%;
    font-size: 2.2rem;
    color: #111;
    font-weight: 900;
    box-shadow: 0 8px 25px rgba(212,175,117,0.4);
    animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.confirm-title { font-size: 2rem; font-family: 'Playfair Display', Georgia, serif; color: #111; margin-bottom: 8px; }
.confirm-subtitle { color: #888; margin-bottom: 30px; }
.confirm-details {
    text-align: left;
    background: #fdfaf5;
    border: 1px solid #e0d8cc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
}
.confirm-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #f0ebe3;
    gap: 20px;
}
.confirm-row:last-child { border-bottom: none; }
.confirm-label { font-size: 0.82rem; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap; }
.confirm-value { font-size: 0.95rem; font-weight: 600; color: #292929; text-align: right; }
.booking-id { font-family: monospace; color: #D4AF75; font-size: 1rem; }
.status-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; }
.status-pending { background: #fff9e6; color: #b8860b; border: 1px solid #f0d060; }
.confirm-note {
    background: #f0fdf4;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 28px;
    color: #2d6a4f;
    font-size: 0.9rem;
    line-height: 1.6;
    text-align: left;
}
.confirm-note p { margin: 0 0 6px; }
.confirm-note p:last-child { margin: 0; }
.confirm-actions { display: flex; flex-direction: column; gap: 12px; }

/* ===== Buttons ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 28px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.04em;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-family: inherit;
}
.btn-lg { padding: 16px 32px; font-size: 1rem; }
.btn-gold { background: #D4AF75; color: #111111; }
.btn-gold:hover { background: #c9a05e; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,175,117,0.4); }
.btn-gold:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.btn-whatsapp { background: #25D366; color: #fff; }
.btn-whatsapp:hover { background: #1ebe5a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,211,102,0.3); }
.btn-outline-gold { background: transparent; color: #D4AF75; border: 1.5px solid #D4AF75; }
.btn-outline-gold:hover { background: #D4AF75; color: #111; }
</style>

<script>
(function() {
    'use strict';

    const form      = document.getElementById('appointmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const alertBox  = document.getElementById('formAlert');
    const instrArea = document.getElementById('instructions');
    const instrCount= document.getElementById('instrCount');

    if (!form) return;

    // ===== Character counter =====
    if (instrArea && instrCount) {
        instrArea.addEventListener('input', function() {
            instrCount.textContent = this.value.length;
        });
    }

    // ===== Payment option styling =====
    document.querySelectorAll('.payment-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-option-inner').forEach(el => el.style.borderColor = '');
            if (this.checked) {
                this.nextElementSibling.style.borderColor = '#D4AF75';
            }
        });
    });

    // ===== Field validation helpers =====
    function showError(fieldId, errorId, msg) {
        const field = document.getElementById(fieldId);
        const errEl = document.getElementById(errorId);
        if (field) field.classList.add('error');
        if (errEl) errEl.textContent = msg;
    }
    function clearError(fieldId, errorId) {
        const field = document.getElementById(fieldId);
        const errEl = document.getElementById(errorId);
        if (field) { field.classList.remove('error'); }
        if (errEl) errEl.textContent = '';
    }
    function markValid(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) { field.classList.remove('error'); field.classList.add('valid'); }
    }

    function loadRazorpayCheckout() {
        if (window.Razorpay) return Promise.resolve();
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = resolve;
            script.onerror = () => reject(new Error('Online payment could not be loaded.'));
            document.head.appendChild(script);
        });
    }

    // Live validation
    document.getElementById('customer_name')?.addEventListener('blur', function() {
        clearError('customer_name','err_name');
        if (this.value.trim().length < 2) {
            showError('customer_name','err_name','Please enter your full name (min 2 characters).');
        } else { markValid('customer_name'); }
    });
    document.getElementById('mobile')?.addEventListener('blur', function() {
        clearError('mobile','err_mobile');
        const v = this.value.replace(/\D/g,'');
        if (!/^[6-9][0-9]{9}$/.test(v)) {
            showError('mobile','err_mobile','Enter a valid 10-digit Indian mobile number.');
        } else { markValid('mobile'); }
    });
    document.getElementById('email')?.addEventListener('blur', function() {
        clearError('email','err_email');
        if (this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
            showError('email','err_email','Please enter a valid email address.');
        } else if (this.value) { markValid('email'); }
    });
    document.getElementById('appt_time')?.addEventListener('change', function() {
        clearError('appt_time','err_time');
        const [h, m] = this.value.split(':').map(Number);
        const mins = h * 60 + m;
        if (mins < 600 || mins > 1200) {
            showError('appt_time','err_time','Please select a time between 10:00 AM and 8:00 PM.');
        } else { markValid('appt_time'); }
    });

    // ===== Form submission =====
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset alerts and errors
        alertBox.style.display = 'none';
        alertBox.className = 'form-alert';
        ['customer_name','mobile','email','service_id','appt_date','appt_time','instructions'].forEach(id => {
            const f = document.getElementById(id);
            if (f) { f.classList.remove('error', 'valid'); }
        });
        ['err_name','err_mobile','err_email','err_service','err_date','err_time','err_instructions','err_payment'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });

        // Client-side validation
        let valid = true;
        const name    = document.getElementById('customer_name').value.trim();
        const mobile  = document.getElementById('mobile').value.replace(/\D/g,'');
        const email   = document.getElementById('email').value.trim();
        const svcId   = document.getElementById('service_id').value;
        const date    = document.getElementById('appt_date').value;
        const time    = document.getElementById('appt_time').value;
        const instr   = document.getElementById('instructions').value.trim();

        if (name.length < 2 || name.length > 100) {
            showError('customer_name','err_name','Please enter your full name (2–100 characters).'); valid = false;
        }
        if (!/^[6-9][0-9]{9}$/.test(mobile)) {
            showError('mobile','err_mobile','Enter a valid 10-digit Indian mobile number.'); valid = false;
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email','err_email','Please enter a valid email address.'); valid = false;
        }
        if (!svcId) {
            showError('service_id','err_service','Please select a service.'); valid = false;
        }
        if (!date) {
            showError('appt_date','err_date','Please select an appointment date.'); valid = false;
        } else {
            const today = new Date(); today.setHours(0,0,0,0);
            const selDate = new Date(date);
            if (selDate < today) {
                showError('appt_date','err_date','Please select today or a future date.'); valid = false;
            }
        }
        if (!time) {
            showError('appt_time','err_time','Please select a preferred time.'); valid = false;
        } else {
            const [h,m] = time.split(':').map(Number);
            const mins = h * 60 + m;
            if (mins < 600 || mins > 1200) {
                showError('appt_time','err_time','Time must be between 10:00 AM and 8:00 PM.'); valid = false;
            }
        }
        if (instr.length > 500) {
            showError('instructions','err_instructions','Instructions must be under 500 characters.'); valid = false;
        }

        if (!valid) {
            alertBox.textContent = 'Please fix the errors above and try again.';
            alertBox.classList.add('alert-error');
            alertBox.style.display = 'block';
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        // Disable button, show loading
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').style.display = 'none';
        submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';

        try {
            const fd = new FormData(form);
            fd.set('mobile', mobile); // ensure clean digits

            const resp = await fetch('api/book_appointment.php', {
                method: 'POST',
                body: fd
            });
            const data = await resp.json();

            if (data.success) {
                if (fd.get('payment_method') === 'razorpay') {
                    try {
                        const paymentForm = new FormData();
                        paymentForm.append('action', 'create_order');
                        paymentForm.append('booking_id', data.booking_id);
                        paymentForm.append('csrf_token', fd.get('csrf_token'));
                        const orderResponse = await fetch('api/payment.php', {
                            method: 'POST',
                            body: paymentForm
                        });
                        const orderData = await orderResponse.json();
                        if (!orderResponse.ok || !orderData.success) {
                            window.alert(orderData.message || 'Online payment could not be started.');
                            window.location.href = 'appointment.php';
                            return;
                        }

                        await loadRazorpayCheckout();
                        const checkout = new window.Razorpay({
                            key: orderData.key_id,
                            amount: orderData.amount,
                            currency: orderData.currency,
                            name: orderData.name,
                            description: orderData.description,
                            order_id: orderData.order_id,
                            prefill: orderData.prefill,
                            notes: { booking_id: orderData.booking_id },
                            modal: {
                                ondismiss: function() {
                                    window.location.href = 'appointment.php';
                                }
                            },
                            handler: async function(payment) {
                                const verifyForm = new FormData();
                                verifyForm.append('action', 'verify_payment');
                                verifyForm.append('booking_id', data.booking_id);
                                verifyForm.append('csrf_token', fd.get('csrf_token'));
                                verifyForm.append('razorpay_payment_id', payment.razorpay_payment_id);
                                verifyForm.append('razorpay_order_id', payment.razorpay_order_id);
                                verifyForm.append('razorpay_signature', payment.razorpay_signature);
                                try {
                                    const verifyResponse = await fetch('api/payment.php', {
                                        method: 'POST',
                                        body: verifyForm
                                    });
                                    const verifyData = await verifyResponse.json();
                                    if (!verifyResponse.ok || !verifyData.success) {
                                        window.alert(verifyData.message || 'We could not verify the payment. Please contact the parlour.');
                                    }
                                } catch (verificationError) {
                                    window.alert('Payment was submitted, but we could not verify it. Please contact the parlour.');
                                }
                                window.location.href = 'appointment.php';
                            }
                        });
                        checkout.on('payment.failed', function() {
                            window.location.href = 'appointment.php';
                        });
                        checkout.open();
                    } catch (paymentError) {
                        window.alert('Your appointment was saved, but online payment could not be started. Please contact the parlour.');
                        window.location.href = 'appointment.php';
                    }
                    return;
                }
                window.location.href = 'appointment.php';
            } else {
                // Show server-side errors
                if (data.errors) {
                    const errMap = {
                        name: ['customer_name','err_name'],
                        mobile: ['mobile','err_mobile'],
                        email: ['email','err_email'],
                        service_id: ['service_id','err_service'],
                        appt_date: ['appt_date','err_date'],
                        appt_time: ['appt_time','err_time'],
                        instructions: ['instructions','err_instructions'],
                    };
                    Object.entries(data.errors).forEach(([key, msg]) => {
                        if (errMap[key]) {
                            showError(errMap[key][0], errMap[key][1], msg);
                        }
                    });
                }
                alertBox.textContent = data.message || 'Something went wrong. Please try again.';
                alertBox.classList.add('alert-error');
                alertBox.style.display = 'block';
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } catch (err) {
            alertBox.textContent = 'Network error. Please check your connection and try again.';
            alertBox.classList.add('alert-error');
            alertBox.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').style.display = 'inline';
            submitBtn.querySelector('.btn-loading').style.display = 'none';
        }
    });

})();
</script>

<?php include 'includes/footer.php'; ?>
