<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();
requireAdmin();

$stats = [
    'appointments' => (int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn(),
    'services' => (int)$pdo->query('SELECT COUNT(*) FROM services WHERE is_active = 1')->fetchColumn(),
    'messages' => (int)$pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn(),
];
$recentStmt = $pdo->query(
    'SELECT a.booking_id, a.appt_date, a.appt_time, a.status, c.name AS customer_name,
            c.mobile, s.name AS service_name
     FROM appointments a
     INNER JOIN customers c ON c.id = a.customer_id
     INNER JOIN services s ON s.id = a.service_id
     ORDER BY a.created_at DESC
     LIMIT 10'
);
$recentAppointments = $recentStmt->fetchAll();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard | Kanak Beauty Parlour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminNavigation">
        <div class="admin-sidebar-logo">
            <span class="logo-text">Kanak Beauty</span>
            <span class="admin-label">Administration</span>
        </div>
        <nav class="admin-nav" aria-label="Admin navigation">
            <p class="admin-nav-section">Workspace</p>
            <a class="admin-nav-link active" href="dashboard.php" aria-current="page"><span class="icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>Dashboard</a>
            <a class="admin-nav-link" href="appointments.php"><span class="icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>Appointments</a>
            <a class="admin-nav-link" href="gallery.php"><span class="icon"><i class="fa-regular fa-images" aria-hidden="true"></i></span>Gallery</a>
            <a class="admin-nav-link" href="../index.php"><span class="icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>View Website</a>
        </nav>
        <div class="admin-sidebar-footer">
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="admin-nav-link" type="submit"><span class="icon"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></span>Sign Out</button>
            </form>
        </div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <button class="admin-mobile-toggle" type="button" aria-label="Open admin navigation" aria-controls="adminNavigation" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
            <h1 class="topbar-title">Dashboard</h1>
            <div class="topbar-right">
                <span class="admin-user-pill"><span class="avatar"><?= htmlspecialchars(strtoupper(substr((string)($_SESSION['admin_name'] ?? 'A'), 0, 1)), ENT_QUOTES, 'UTF-8') ?></span><?= htmlspecialchars((string)($_SESSION['admin_name'] ?? 'Administrator'), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </header>
        <div class="admin-content">
            <section class="admin-stats-grid" aria-label="Business overview">
                <article class="stat-card"><div class="stat-card-info"><p class="label">All Appointments</p><p class="value"><?= $stats['appointments'] ?></p></div><div class="stat-card-icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></div></article>
                <article class="stat-card"><div class="stat-card-info"><p class="label">Awaiting Confirmation</p><p class="value"><?= $stats['pending'] ?></p></div><div class="stat-card-icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></div></article>
                <article class="stat-card"><div class="stat-card-info"><p class="label">Active Services</p><p class="value"><?= $stats['services'] ?></p></div><div class="stat-card-icon"><i class="fa-solid fa-spa" aria-hidden="true"></i></div></article>
                <article class="stat-card"><div class="stat-card-info"><p class="label">Unread Messages</p><p class="value"><?= $stats['messages'] ?></p></div><div class="stat-card-icon"><i class="fa-regular fa-envelope" aria-hidden="true"></i></div></article>
            </section>
            <section class="admin-card">
                <div class="admin-card-header">
                    <h4>Recent Appointments</h4>
                    <span>Latest 10 bookings</span>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr><th>Booking ID</th><th>Customer</th><th>Service</th><th>Date &amp; Time</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($recentAppointments): ?>
                            <?php foreach ($recentAppointments as $appointment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($appointment['booking_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($appointment['customer_name'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars($appointment['mobile'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td><?= htmlspecialchars($appointment['service_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(formatDate($appointment['appt_date']), ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars(formatTime($appointment['appt_time']), ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td><?= getStatusBadge($appointment['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No appointments have been received yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
<script src="../assets/js/main.js" defer></script>
</body>
</html>
