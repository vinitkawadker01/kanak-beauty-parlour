<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startAppSession();
$settings = getSettings($pdo);
$pageTitle = 'Contact | Kanak Beauty Parlour Betul';
$pageDesc = 'Contact Kanak Beauty Parlour in Betul to ask a question or plan your beauty appointment.';
$currentPage = 'contact';
$formValues = ['name' => '', 'mobile' => '', 'email' => '', 'message' => ''];
$formError = '';
$formSuccess = !empty($_SESSION['contact_success']);
unset($_SESSION['contact_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formValues as $field => $value) {
        $submittedValue = $_POST[$field] ?? '';
        $formValues[$field] = is_string($submittedValue) ? trim($submittedValue) : '';
    }

    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !verifyCSRFToken($submittedToken)) {
        $formError = 'Your session expired. Please refresh the page and try again.';
    } elseif (mb_strlen($formValues['name']) < 2 || mb_strlen($formValues['name']) > 100) {
        $formError = 'Please enter your name (2–100 characters).';
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', preg_replace('/\D+/', '', $formValues['mobile']))) {
        $formError = 'Please enter a valid 10-digit Indian mobile number.';
    } elseif ($formValues['email'] !== '' && !filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } elseif (mb_strlen($formValues['message']) < 5 || mb_strlen($formValues['message']) > 3000) {
        $formError = 'Please enter a message between 5 and 3,000 characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, mobile, email, message) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $formValues['name'],
                preg_replace('/\D+/', '', $formValues['mobile']),
                $formValues['email'] !== '' ? $formValues['email'] : null,
                $formValues['message'],
            ]);
            $_SESSION['contact_success'] = true;
            header('Location: contact.php#contact-form', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log('Contact message could not be saved: ' . $exception->getMessage());
            $formError = 'We could not send your message right now. Please call us instead.';
        }
    }
}

$csrfToken = generateCSRFToken();
include 'includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <div class="hero-content text-center">
            <p class="hero-subtitle">We’re Here to Help</p>
            <h1 class="hero-title">Get in Touch</h1>
            <p class="hero-description">Ask a question, discuss a service, or tell us how we can help.</p>
        </div>
    </div>
</section>
<section class="section-padding">
    <div class="container">
        <div class="contact-info-grid">
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-phone" aria-hidden="true"></i></div><h4>Call Us</h4><p><a href="tel:<?= htmlspecialchars(preg_replace('/\D+/', '', $settings['phone'] ?? '8839131870'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($settings['phone'] ?? '8839131870', ENT_QUOTES, 'UTF-8') ?></a></p></article>
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div><h4>Visit Us</h4><p><?= htmlspecialchars($settings['address'] ?? 'Chandrashekhar Ward, Sadar, Betul, Madhya Pradesh, Near Sai Mandir', ENT_QUOTES, 'UTF-8') ?></p></article>
            <article class="contact-info-card"><div class="icon-wrap"><i class="fa-solid fa-clock" aria-hidden="true"></i></div><h4>Opening Hours</h4><p><?= htmlspecialchars($settings['business_hours'] ?? '10:00 AM – 8:00 PM', ENT_QUOTES, 'UTF-8') ?></p></article>
        </div>
        <div class="contact-form-layout" id="contact-form">
            <div class="contact-form-card">
                <div class="section-header">
                    <span class="sub-label">Send a message</span>
                    <h2>How Can We Help?</h2>
                </div>
                <?php if ($formSuccess): ?>
                    <div class="form-alert alert-success" role="status">Thank you. Your message has been received, and our team will be in touch.</div>
                <?php endif; ?>
                <?php if ($formError !== ''): ?>
                    <div class="form-alert alert-error" role="alert"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="contact.php" class="contact-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-row-2">
                        <label class="form-group">Your Name
                            <input class="form-input" type="text" name="name" maxlength="100" autocomplete="name" required value="<?= htmlspecialchars($formValues['name'], ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                        <label class="form-group">Mobile Number
                            <input class="form-input" type="tel" name="mobile" inputmode="numeric" maxlength="15" autocomplete="tel" required value="<?= htmlspecialchars($formValues['mobile'], ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                    </div>
                    <label class="form-group">Email <span class="optional">(optional)</span>
                        <input class="form-input" type="email" name="email" maxlength="100" autocomplete="email" value="<?= htmlspecialchars($formValues['email'], ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label class="form-group">Message
                        <textarea class="form-textarea" name="message" rows="6" maxlength="3000" required><?= htmlspecialchars($formValues['message'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>
                    <button class="btn btn-gold" type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
