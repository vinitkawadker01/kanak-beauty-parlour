<?php
$pageTitle = $pageTitle ?? 'Kanak Beauty Parlour | Betul';
$pageDesc = $pageDesc ?? 'Beauty That Makes You Shine. Professional beauty and makeup services in Betul, Madhya Pradesh.';
$currentPage = $currentPage ?? '';
$businessName = $settings['business_name'] ?? 'Kanak Beauty Parlour';
$tagline = $settings['tagline'] ?? 'Beauty That Makes You Shine.';
$navigation = [
    'index' => ['Home', 'index.php'],
    'about' => ['About', 'about.php'],
    'services' => ['Services', 'services.php'],
    'gallery' => ['Gallery', 'gallery.php'],
    'contact' => ['Contact', 'contact.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#111111">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="navbar">
    <a class="navbar-brand" href="index.php" aria-label="<?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8') ?> home">
        <span class="logo-icon" aria-hidden="true"><i class="fa-solid fa-spa"></i></span>
        <span>
            <span class="logo-text"><?= htmlspecialchars($businessName, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="logo-tagline"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></span>
        </span>
    </a>
    <nav class="navbar-nav" aria-label="Main navigation">
        <?php foreach ($navigation as $page => [$label, $href]): ?>
            <a class="nav-link<?= $currentPage === $page ? ' active' : '' ?>" href="<?= $href ?>"<?= $currentPage === $page ? ' aria-current="page"' : '' ?>><?= $label ?></a>
        <?php endforeach; ?>
        <a class="nav-link nav-cta<?= $currentPage === 'appointment' ? ' active' : '' ?>" href="appointment.php">Book Now</a>
    </nav>
    <button class="hamburger" id="menuToggle" type="button" aria-label="Open navigation menu" aria-controls="mobileNavigation" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</header>
<nav class="mobile-nav" id="mobileNavigation" aria-label="Mobile navigation">
    <?php foreach ($navigation as $page => [$label, $href]): ?>
        <a class="nav-link<?= $currentPage === $page ? ' active' : '' ?>" href="<?= $href ?>"<?= $currentPage === $page ? ' aria-current="page"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
    <a class="nav-link nav-cta<?= $currentPage === 'appointment' ? ' active' : '' ?>" href="appointment.php">Book an Appointment</a>
</nav>
<main id="main-content">
