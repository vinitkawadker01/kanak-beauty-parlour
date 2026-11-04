<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();
requireAdmin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !verifyCSRFToken($token)) {
        $error = 'Invalid request. Refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if (!is_string($action)) {
            $error = 'Choose a valid gallery action.';
        } elseif ($action === 'upload') {
            $title = $_POST['title'] ?? '';
            $category = $_POST['category'] ?? '';
            if (!is_string($title) || !is_string($category)) {
                $error = 'Enter a valid image title and category.';
            } else {
                $title = trim($title);
                $category = trim($category);
                if (strlen($title) > 150 || strlen($category) < 2 || strlen($category) > 80) {
                    $error = 'Use a title of at most 150 characters and a category of 2–80 characters.';
                } elseif (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
                    $error = 'Choose an image to upload.';
                } else {
                    $imagePath = '';
                    try {
                        $filename = uploadImage(
                            $_FILES['image'],
                            APP_ROOT . '/assets/uploads/gallery',
                            ['jpg', 'jpeg', 'png', 'webp']
                        );
                        $imagePath = 'assets/uploads/gallery/' . $filename;
                        $insert = $pdo->prepare(
                            'INSERT INTO gallery (title, image, category, is_active) VALUES (?, ?, ?, 1)'
                        );
                        $insert->execute([$title !== '' ? $title : null, $imagePath, $category]);
                        $_SESSION['admin_notice'] = 'Gallery image uploaded.';
                        header('Location: gallery.php');
                        exit;
                    } catch (Exception $exception) {
                        if ($imagePath !== '') {
                            deleteImage(APP_ROOT . '/' . $imagePath);
                        }
                        error_log('Gallery image upload failed: ' . $exception->getMessage());
                        $error = 'Image upload failed. Check the image type, 5 MB size limit, and upload folder permissions.';
                    }
                }
            }
        } elseif ($action === 'delete') {
            $imageId = filter_var($_POST['image_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$imageId || $imageId < 1) {
                $error = 'Choose a valid gallery image.';
            } else {
                $find = $pdo->prepare('SELECT image FROM gallery WHERE id = ? LIMIT 1');
                $find->execute([$imageId]);
                $imagePath = $find->fetchColumn();
                if ($imagePath === false) {
                    $error = 'That gallery image could not be found.';
                } else {
                    $delete = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
                    $delete->execute([$imageId]);
                    if (is_string($imagePath) && strpos($imagePath, 'assets/uploads/gallery/') === 0) {
                        if (!deleteImage(APP_ROOT . '/' . $imagePath)) {
                            error_log('Gallery record was deleted but its uploaded image could not be removed: ' . $imagePath);
                        }
                    }
                    $_SESSION['admin_notice'] = 'Gallery image removed.';
                    header('Location: gallery.php');
                    exit;
                }
            }
        } else {
            $error = 'Unsupported gallery action.';
        }
    }
}

$notice = (string)($_SESSION['admin_notice'] ?? '');
unset($_SESSION['admin_notice']);
$images = $pdo->query(
    'SELECT id, title, image, category, is_active FROM gallery ORDER BY sort_order ASC, created_at DESC'
)->fetchAll();
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gallery | Kanak Beauty Parlour</title>
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
            <a class="admin-nav-link" href="appointments.php"><span class="icon"><i class="fa-regular fa-calendar-check" aria-hidden="true"></i></span>Appointments</a>
            <a class="admin-nav-link active" href="gallery.php" aria-current="page"><span class="icon"><i class="fa-regular fa-images" aria-hidden="true"></i></span>Gallery</a>
            <a class="admin-nav-link" href="../index.php"><span class="icon"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>View Website</a>
        </nav>
        <div class="admin-sidebar-footer"><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><button class="admin-nav-link" type="submit"><span class="icon"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></span>Sign Out</button></form></div>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar"><button class="admin-mobile-toggle" type="button" aria-label="Open admin navigation" aria-controls="adminNavigation" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button><h1 class="topbar-title">Gallery</h1><span class="admin-user-pill"><?= htmlspecialchars((string)($_SESSION['admin_name'] ?? 'Administrator'), ENT_QUOTES, 'UTF-8') ?></span></header>
        <div class="admin-content">
            <?php if ($notice !== ''): ?><div class="form-alert alert-success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="form-alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <section class="admin-card">
                <div class="admin-card-header"><h4>Add a gallery image</h4></div>
                <form method="post" action="gallery.php" enctype="multipart/form-data" class="admin-gallery-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="upload">
                    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                    <div class="form-row-2">
                        <label class="form-group">Title (optional)<input class="form-input" type="text" name="title" maxlength="150"></label>
                        <label class="form-group">Category<input class="form-input" type="text" name="category" maxlength="80" required placeholder="Bridal, Party, Hairstyling"></label>
                    </div>
                    <label class="form-group">Image (JPEG, PNG, or WebP; maximum 5 MB)<input class="form-input" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required></label>
                    <button class="btn btn-gold" type="submit">Upload Image</button>
                </form>
            </section>
            <section class="admin-card">
                <div class="admin-card-header"><h4>Portfolio images</h4><span><?= count($images) ?> total</span></div>
                <div class="admin-gallery-grid">
                    <?php foreach ($images as $image): ?>
                        <article class="admin-gallery-item">
                            <?php
                            $imageSource = (string)$image['image'];
                            if (!preg_match('~^https?://~i', $imageSource)) {
                                $imageSource = '../' . ltrim($imageSource, '/');
                            }
                            ?>
                            <img src="<?= htmlspecialchars($imageSource, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($image['title'] ?? $image['category'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <div><strong><?= htmlspecialchars($image['title'] ?: 'Untitled', ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($image['category'], ENT_QUOTES, 'UTF-8') ?></span></div>
                            <form method="post" action="gallery.php" onsubmit="return confirm('Remove this image from the gallery?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="image_id" value="<?= (int)$image['id'] ?>">
                                <button class="btn btn-sm btn-danger" type="submit">Remove</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                    <?php if (!$images): ?><p class="text-center">No gallery images yet.</p><?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</div>
<script src="../assets/js/main.js" defer></script>
</body>
</html>
