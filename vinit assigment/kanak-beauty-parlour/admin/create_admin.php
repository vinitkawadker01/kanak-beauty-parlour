<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

function fail(string $message, int $exitCode = 1): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($exitCode);
}

if ($argc !== 4) {
    fail('Usage: php admin/create_admin.php <username> <email> "<full name>"');
}

$username = trim($argv[1]);
$email = trim($argv[2]);
$fullName = trim($argv[3]);
if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
    fail('Username must be 3–50 characters and use only letters, numbers, dots, underscores, or hyphens.');
}
if (strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fail('Enter a valid email address (maximum 100 characters).');
}
if (strlen($fullName) < 2 || strlen($fullName) > 100) {
    fail('Full name must be between 2 and 100 characters.');
}

fwrite(STDERR, 'Enter a new admin password (at least 12 characters): ');
$password = fgets(STDIN);
if ($password === false) {
    fail('No password was received on standard input.');
}
$password = rtrim($password, "\r\n");
if (strlen($password) < 12 || strlen($password) > 4096) {
    fail('Password must be at least 12 characters and no more than 4096 bytes.');
}
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
unset($password);
if ($passwordHash === false) {
    fail('The password could not be securely hashed.');
}

$lockName = 'kanak_beauty_first_admin';
$lockStmt = $pdo->prepare('SELECT GET_LOCK(?, 10)');
$lockStmt->execute([$lockName]);
if ((int)$lockStmt->fetchColumn() !== 1) {
    fail('Could not obtain the first-admin setup lock. Try again.');
}

try {
    $activeAdmins = (int)$pdo->query('SELECT COUNT(*) FROM admins WHERE is_active = 1')->fetchColumn();
    if ($activeAdmins !== 0) {
        fail('An active admin already exists. No account was created.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO admins (username, email, password, full_name) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$username, $email, $passwordHash, $fullName]);
    fwrite(STDOUT, "First admin account created. Sign in at /admin/login.php.\n");
} catch (PDOException $exception) {
    error_log('First admin account creation failed: ' . $exception->getMessage());
    fail('The admin account could not be created. Check for an existing username/email and review the PHP error log.');
} finally {
    $releaseStmt = $pdo->prepare('SELECT RELEASE_LOCK(?)');
    $releaseStmt->execute([$lockName]);
}
