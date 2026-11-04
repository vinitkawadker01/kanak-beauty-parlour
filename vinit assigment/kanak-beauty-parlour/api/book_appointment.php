<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function postString(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(['success' => false, 'message' => 'This endpoint only accepts appointment requests.'], 405);
}

if (!verifyCSRFToken(postString('csrf_token'))) {
    respond(['success' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 403);
}

$name = trim(postString('customer_name'));
$mobile = preg_replace('/\D+/', '', postString('mobile'));
$email = trim(postString('email'));
$serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
$date = trim(postString('appt_date'));
$time = trim(postString('appt_time'));
$instructions = trim(postString('instructions'));
$paymentMethod = postString('payment_method');
$errors = [];

if (strlen($name) < 2 || strlen($name) > 100) {
    $errors['name'] = 'Please enter your full name (2–100 characters).';
}
if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $errors['mobile'] = 'Enter a valid 10-digit Indian mobile number.';
}
if ($email !== '' && (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    $errors['email'] = 'Please enter a valid email address.';
}
if (!$serviceId || $serviceId < 1) {
    $errors['service_id'] = 'Please select a valid service.';
}

$dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
$dateErrors = DateTimeImmutable::getLastErrors();
if (
    !$dateValue
    || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
    || $dateValue->format('Y-m-d') !== $date
    || $dateValue < new DateTimeImmutable('today')
    || $dateValue > new DateTimeImmutable('+90 days')
) {
    $errors['appt_date'] = 'Choose a valid date within the next 90 days.';
}

if (!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
    $errors['appt_time'] = 'Choose a time between 10:00 AM and 8:00 PM.';
} else {
    [$hour, $minute] = array_map('intval', explode(':', $time));
    $minutes = ($hour * 60) + $minute;
    if ($minutes < 600 || $minutes > 1200) {
        $errors['appt_time'] = 'Choose a time between 10:00 AM and 8:00 PM.';
    }
}
if (strlen($instructions) > 500) {
    $errors['instructions'] = 'Instructions must be 500 characters or fewer.';
}
if (!in_array($paymentMethod, ['cash', 'upi', 'razorpay'], true)) {
    $errors['payment_method'] = 'Choose a valid payment method.';
}
if ($errors) {
    respond(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
}

try {
    $serviceStmt = $pdo->prepare(
        'SELECT id, name, price FROM services WHERE id = ? AND is_active = 1 LIMIT 1'
    );
    $serviceStmt->execute([$serviceId]);
    $service = $serviceStmt->fetch();
    if (!$service) {
        respond([
            'success' => false,
            'message' => 'That service is no longer available. Please choose another service.',
            'errors' => ['service_id' => 'Please choose an available service.'],
        ], 422);
    }
    if ($paymentMethod === 'razorpay' && (float)($service['price'] ?? 0) < 1) {
        respond([
            'success' => false,
            'message' => 'Please contact the parlour to confirm the service price before paying online.',
            'errors' => ['payment_method' => 'Online payment requires a confirmed service price.'],
        ], 422);
    }

    $pdo->beginTransaction();

    $customerStmt = $pdo->prepare('SELECT id FROM customers WHERE mobile = ? LIMIT 1 FOR UPDATE');
    $customerStmt->execute([$mobile]);
    $customer = $customerStmt->fetch();

    if ($customer) {
        $customerId = (int)$customer['id'];
        $updateCustomer = $pdo->prepare('UPDATE customers SET name = ?, email = ? WHERE id = ?');
        $updateCustomer->execute([$name, $email !== '' ? $email : null, $customerId]);
    } else {
        $insertCustomer = $pdo->prepare('INSERT INTO customers (name, mobile, email) VALUES (?, ?, ?)');
        $insertCustomer->execute([$name, $mobile, $email !== '' ? $email : null]);
        $customerId = (int)$pdo->lastInsertId();
    }

    $bookingId = generateBookingId();
    $appointmentStmt = $pdo->prepare(
        "INSERT INTO appointments
            (booking_id, customer_id, service_id, appt_date, appt_time, instructions, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')"
    );
    $appointmentStmt->execute([
        $bookingId,
        $customerId,
        (int)$service['id'],
        $date,
        $time . ':00',
        $instructions !== '' ? $instructions : null,
    ]);

    $appointmentId = (int)$pdo->lastInsertId();
    $amount = $service['price'] !== null ? (float)$service['price'] : 0.00;
    $paymentStmt = $pdo->prepare(
        "INSERT INTO payments (appointment_id, amount, method, status)
         VALUES (?, ?, ?, 'pending')"
    );
    $paymentStmt->execute([$appointmentId, $amount, $paymentMethod]);
    $pdo->commit();

    $_SESSION['booking_confirmed'] = [
        'booking_id' => $bookingId,
        'customer_name' => $name,
        'service_name' => $service['name'],
        'appt_date' => $date,
        'appt_time' => $time,
        'mobile' => $mobile,
        'payment_method' => $paymentMethod,
        'payment_status' => 'pending',
    ];

    respond(['success' => true, 'booking_id' => $bookingId]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Appointment booking failed: ' . $exception->getMessage());
    respond(['success' => false, 'message' => 'We could not save your appointment. Please try again or contact us.'], 500);
}
