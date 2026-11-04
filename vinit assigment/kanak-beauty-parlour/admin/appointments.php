<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();
requireAdmin();

$allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $bookingId = $_POST['booking_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $adminNotes = $_POST['admin_notes'] ?? '';

    if (
        !is_string($token)
        || !verifyCSRFToken($token)
        || !is_string($bookingId)
        || !is_string($status)
        || !is_string($adminNotes)
    ) {
        $error = 'Invalid request. Refresh the page and try again.';
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $error = 'Choose a valid appointment status.';
    } elseif (strlen($adminNotes) > 5000) {
        $error = 'Admin notes must be 5,000 characters or fewer.';
    } else {
        $exists = $pdo->prepare('SELECT id FROM appointments WHERE booking_id = ? LIMIT 1');
        $exists->execute([$bookingId]);
        $appointmentId = $exists->fetchColumn();
        if ($appointmentId === false) {
            $error = 'That appointment could not be found.';
        } else {
            $update = $pdo->prepare(
                'UPDATE appointments SET status = ?, admin_notes = ? WHERE id = ?'
            );
            $update->execute([
                $status,
                trim($adminNotes) !== '' ? trim($adminNotes) : null,
                (int)$appointmentId,
            ]);
            $_SESSION['admin_notice'] = 'Appointment ' . $bookingId . ' updated.';
            header('Location: appointments.php');
            exit;
        }
    }
}

$notice = (string)($_SESSION['admin_notice'] ?? '');
unset($_SESSION['admin_notice']);
$pageValue = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = $pageValue !== false ? max(1, $pageValue) : 1;
$perPage = 20;
$total = (int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$pagination = paginate($total, $perPage, $page);
$stmt = $pdo->prepare(
    'SELECT a.id, a.booking_id, a.appt_date, a.appt_time, a.instructions, a.admin_notes, a.status,
            c.name AS customer_name, c.mobile, c.email, s.name AS service_name,
            p.amount, p.method AS payment_method, p.status AS payment_status
     FROM appointments a
     INNER JOIN customers c ON c.id = a.customer_id
     INNER JOIN services s ON s.id = a.service_id
     LEFT JOIN payments p ON p.appointment_id = a.id
     ORDER BY a.appt_date DESC, a.appt_time DESC, a.id DESC
     LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Appointments | Kanak Beauty Parlour</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminNavigation">
        <div class="admin-sidebar-logo"><span class="logo-text">Kanak Beauty</span><span class="admin-label">Administration</span></div>
        <nav class="admin-nav" aria-label="Admin navigation">
            <p class="admin-nav-section">Workspace</p>
            <a class="admin-nav-link" href="dashboard.php"><span class="icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>Dashboard</a>
            <a class="admin-nav-link active" href="appointments.php" aria-current="page"><span class="icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>Appointments</a>
            <a class="admin-nav-link" href="gallery.php"><span class="icon"><i class="fa-regular fa-images" aria-hidden="true"></i></span>Gallery</a>
            <a class="admin-nav-link" href="../index.php"><span class="icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>View Website</a>
        </nav>
        <div class="admin-sidebar-footer"><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><button class="admin-nav-link" type="submit"><span class="icon"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></span>Sign Out</button></form></div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar"><button class="admin-mobile-toggle" type="button" aria-label="Open admin navigation" aria-controls="adminNavigation" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button><h1 class="topbar-title">Appointments</h1><span class="admin-user-pill"><?= htmlspecialchars((string)($_SESSION['admin_name'] ?? 'Administrator'), ENT_QUOTES, 'UTF-8') ?></span></header>
        <div class="admin-content">
            <?php if ($notice !== ''): ?><div class="form-alert alert-success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="form-alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-card">
                <div class="admin-card-header"><h4>Bookings</h4><span><?= $total ?> total</span></div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Booking</th><th>Customer</th><th>Service</th><th>Date &amp; Time</th><th>Payment</th><th>Update status</th></tr></thead>
                        <tbody>
                        <?php if (!$appointments): ?>
                            <tr><td colspan="6" class="text-center">No appointments have been received yet.</td></tr>
                        <?php else: foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars($appointment['booking_id'], ENT_QUOTES, 'UTF-8') ?><br><?= getStatusBadge($appointment['status']) ?></td>
                                <td><?= htmlspecialchars($appointment['customer_name'], ENT_QUOTES, 'UTF-8') ?><br><a href="tel:<?= htmlspecialchars($appointment['mobile'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($appointment['mobile'], ENT_QUOTES, 'UTF-8') ?></a><?php if ($appointment['email']): ?><br><?= htmlspecialchars($appointment['email'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></td>
                                <td><?= htmlspecialchars($appointment['service_name'], ENT_QUOTES, 'UTF-8') ?><?php if ($appointment['instructions']): ?><br><small><?= htmlspecialchars($appointment['instructions'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></td>
                                <td><?= htmlspecialchars(formatDate($appointment['appt_date']), ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars(formatTime($appointment['appt_time']), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(ucfirst($appointment['payment_method'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars(ucfirst($appointment['payment_status'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="post" action="appointments.php" class="admin-status-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($appointment['booking_id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <label class="sr-only" for="status-<?= (int)$appointment['id'] ?>">Status for <?= htmlspecialchars($appointment['booking_id'], ENT_QUOTES, 'UTF-8') ?></label>
                                        <select name="status" id="status-<?= (int)$appointment['id'] ?>" class="form-select">
                                            <?php foreach ($allowedStatuses as $status): ?>
                                                <option value="<?= $status ?>"<?= $appointment['status'] === $status ? ' selected' : '' ?>><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label class="sr-only" for="notes-<?= (int)$appointment['id'] ?>">Admin notes for <?= htmlspecialchars($appointment['booking_id'], ENT_QUOTES, 'UTF-8') ?></label>
                                        <textarea class="form-textarea" id="notes-<?= (int)$appointment['id'] ?>" name="admin_notes" maxlength="5000" rows="2" placeholder="Private admin notes"><?= htmlspecialchars($appointment['admin_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <button class="btn btn-sm btn-gold" type="submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php if ($pagination['last_page'] > 1): ?>
                <nav class="admin-pagination" aria-label="Appointment pages">
                    <?php if ($pagination['has_prev']): ?><a class="btn btn-sm btn-outline-gold" href="?page=<?= $pagination['prev_page'] ?>">Previous</a><?php endif; ?>
                    <span>Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?></span>
                    <?php if ($pagination['has_next']): ?><a class="btn btn-sm btn-outline-gold" href="?page=<?= $pagination['next_page'] ?>">Next</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../assets/js/main.js" defer></script>
</body>
</html>
