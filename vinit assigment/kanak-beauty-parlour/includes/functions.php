<?php
/**
 * Core Helper Functions — Kanak Beauty Parlour
 * All utility functions used throughout the application.
 */

// ============================================================
// INPUT / SECURITY
// ============================================================

/**
 * Start a session with cookie-only, HTTP-only, same-site cookies.
 */
function startAppSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Sanitize a string input: trim whitespace and escape HTML special chars.
 *
 * @param  string $input  Raw user input
 * @return string         Sanitized string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirect the browser to the given URL and terminate.
 *
 * @param  string $url  Destination URL (absolute or root-relative)
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ============================================================
// BOOKING & TOKENS
// ============================================================

/**
 * Generate a booking ID in the format KBP-YYYYMMDD-XXXXXX.
 *
 * @return string  e.g. "KBP-20261104-078321"
 */
function generateBookingId(): string
{
    $date   = date('Ymd');
    $random = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    return "KBP-{$date}-{$random}";
}

/**
 * Generate (or retrieve) a CSRF token for the current session.
 *
 * @return string  Hex CSRF token
 */
function generateCSRFToken(): string
{
    startAppSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify that the submitted CSRF token matches the session token.
 *
 * @param  string $token  Token from form submission
 * @return bool           True if valid
 */
function verifyCSRFToken(string $token): bool
{
    startAppSession();
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================
// SETTINGS
// ============================================================

/**
 * Fetch all rows from business_settings and return as key => value array.
 *
 * @param  PDO   $pdo
 * @return array<string, string>
 */
function getSettings(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM business_settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * Fetch a single setting value by key with an optional default.
 *
 * @param  PDO    $pdo
 * @param  string $key
 * @param  string $default  Fallback when key not found
 * @return string
 */
function getSetting(PDO $pdo, string $key, string $default = ''): string
{
    $stmt = $pdo->prepare(
        "SELECT setting_value FROM business_settings WHERE setting_key = ? LIMIT 1"
    );
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

// ============================================================
// SERVICES
// ============================================================

/**
 * Fetch all active services ordered by sort_order then name.
 *
 * @param  PDO   $pdo
 * @return array
 */
function getActiveServices(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT * FROM services
         WHERE is_active = 1
         ORDER BY sort_order ASC, name ASC"
    );
    return $stmt->fetchAll();
}

// ============================================================
// GALLERY
// ============================================================

/**
 * Fetch gallery images, optionally filtered by category.
 *
 * @param  PDO         $pdo
 * @param  string|null $category  Optional category slug/name
 * @return array
 */
function getGalleryImages(PDO $pdo, ?string $category = null): array
{
    if ($category !== null) {
        $stmt = $pdo->prepare(
            "SELECT * FROM gallery
             WHERE is_active = 1 AND category = ?
             ORDER BY sort_order ASC, created_at DESC"
        );
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query(
            "SELECT * FROM gallery
             WHERE is_active = 1
             ORDER BY sort_order ASC, created_at DESC"
        );
    }
    return $stmt->fetchAll();
}

// ============================================================
// REVIEWS
// ============================================================

/**
 * Fetch all active / approved reviews ordered by newest first.
 *
 * @param  PDO   $pdo
 * @return array
 */
function getActiveReviews(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT * FROM reviews
         WHERE is_active = 1
         ORDER BY created_at DESC"
    );
    return $stmt->fetchAll();
}

// ============================================================
// FORMATTING
// ============================================================

/**
 * Format a numeric price as Indian Rupee string.
 * Returns "On Request" when price is 0 or null.
 *
 * @param  float|int|null $price
 * @return string   e.g. "₹1,500" or "On Request"
 */
function formatPrice($price): string
{
    if ($price === null || (float)$price === 0.0) {
        return 'On Request';
    }
    return '₹' . number_format((float)$price, 0, '.', ',');
}

/**
 * Format a date string into human-readable form.
 *
 * @param  string $date  MySQL DATE string e.g. "2026-11-04"
 * @return string        e.g. "04 Nov 2026"
 */
function formatDate(string $date): string
{
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return date('d M Y', $ts);
}

/**
 * Format a time string into 12-hour AM/PM format.
 *
 * @param  string $time  MySQL TIME string e.g. "10:00:00"
 * @return string        e.g. "10:00 AM"
 */
function formatTime(string $time): string
{
    $ts = strtotime($time);
    if ($ts === false) {
        return $time;
    }
    return date('h:i A', $ts);
}

/**
 * Return an HTML <span> badge for a booking/order status.
 *
 * @param  string $status  e.g. 'pending', 'confirmed', 'completed', 'cancelled'
 * @return string          HTML string
 */
function getStatusBadge(string $status): string
{
    $map = [
        'pending'   => ['warning',   'Pending'],
        'confirmed' => ['primary',   'Confirmed'],
        'completed' => ['success',   'Completed'],
        'cancelled' => ['danger',    'Cancelled'],
        'no_show'   => ['secondary', 'No Show'],
        'paid'      => ['success',   'Paid'],
        'refunded'  => ['info',      'Refunded'],
    ];

    $key    = strtolower(trim($status));
    $color  = $map[$key][0]  ?? 'secondary';
    $label  = $map[$key][1]  ?? ucfirst($status);

    return '<span class="badge bg-' . $color . '">' . htmlspecialchars($label) . '</span>';
}

// ============================================================
// AUTHENTICATION
// ============================================================

/**
 * Check whether an admin user is currently logged in via session.
 *
 * @return bool
 */
function isAdmin(): bool
{
    startAppSession();
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_logged_in'])) {
        return false;
    }

    $lastActivity = (int)($_SESSION['admin_last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > 1800) {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_username'],
            $_SESSION['admin_logged_in'],
            $_SESSION['admin_last_activity']
        );
        return false;
    }

    $_SESSION['admin_last_activity'] = time();
    return true;
}

/**
 * Redirect to the admin login page if no admin session is active.
 *
 * @return void
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        redirect('login.php');
    }

    global $pdo;
    $stmt = $pdo->prepare('SELECT is_active FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    if ((int)$stmt->fetchColumn() !== 1) {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_username'],
            $_SESSION['admin_logged_in'],
            $_SESSION['admin_last_activity']
        );
        redirect('login.php');
    }
}

// ============================================================
// FILE / IMAGE HANDLING
// ============================================================

/**
 * Securely handle an uploaded image file.
 *
 * @param  array    $file     Entry from $_FILES (single file)
 * @param  string   $dir      Destination directory (absolute path, no trailing slash)
 * @param  string[] $allowed  Allowed extensions
 * @param  int      $maxSize  Maximum byte size (default 5 MB)
 * @return string             Saved filename (not full path)
 * @throws Exception          On validation or write failure
 */
function uploadImage(
    array $file,
    string $dir,
    array $allowed = ['jpg', 'jpeg', 'png', 'webp'],
    int $maxSize = 5242880
): string {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        ];
        throw new Exception($errors[$file['error']] ?? 'Unknown upload error.');
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds the maximum allowed size of ' . ($maxSize / 1048576) . ' MB.');
    }

    // Validate MIME type via finfo (not trusting browser-supplied type)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $validMimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowed));
    }
    if (!isset($validMimes[$ext]) || $validMimes[$ext] !== $mimeType) {
        throw new Exception('File MIME type does not match its extension. Upload rejected.');
    }

    // Ensure destination directory exists and is writable
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            throw new Exception('Could not create upload directory.');
        }
    }
    if (!is_writable($dir)) {
        throw new Exception('Upload directory is not writable.');
    }

    // Generate a unique filename to prevent overwrites and path traversal
    $newFilename = bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;
    $destination = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file to destination.');
    }

    return $newFilename;
}

/**
 * Safely delete an uploaded image file from the filesystem.
 *
 * @param  string $path  Absolute path to the image file
 * @return bool          True on success, false if not found or not a file
 */
function deleteImage(string $path): bool
{
    if (empty($path) || !file_exists($path) || !is_file($path)) {
        return false;
    }
    // Extra safety: only delete files inside the uploads directory
    $uploadsDir = realpath(APP_ROOT . '/assets/uploads');
    $realPath   = realpath($path);
    if ($uploadsDir && $realPath && strpos($realPath, $uploadsDir) === 0) {
        return unlink($realPath);
    }
    return false;
}

// ============================================================
// PAGINATION
// ============================================================

/**
 * Calculate pagination metadata.
 *
 * @param  int $total    Total number of records
 * @param  int $perPage  Records per page
 * @param  int $page     Current page number (1-based)
 * @return array{
 *   total: int,
 *   per_page: int,
 *   current_page: int,
 *   last_page: int,
 *   offset: int,
 *   has_prev: bool,
 *   has_next: bool,
 *   prev_page: int,
 *   next_page: int
 * }
 */
function paginate(int $total, int $perPage, int $page): array
{
    $perPage   = max(1, $perPage);
    $lastPage  = (int)ceil($total / $perPage);
    $lastPage  = max(1, $lastPage);
    $page      = max(1, min($page, $lastPage));
    $offset    = ($page - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $page,
        'last_page'    => $lastPage,
        'offset'       => $offset,
        'has_prev'     => $page > 1,
        'has_next'     => $page < $lastPage,
        'prev_page'    => max(1, $page - 1),
        'next_page'    => min($lastPage, $page + 1),
    ];
}
