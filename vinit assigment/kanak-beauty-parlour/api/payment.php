<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/functions.php';
startAppSession();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function paymentResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function paymentPostString(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function razorpayRequest(string $method, string $path, ?array $payload = null): ?array
{
    $keyId = getenv('RAZORPAY_KEY_ID');
    $keySecret = getenv('RAZORPAY_KEY_SECRET');
    if (!$keyId || !$keySecret) {
        paymentResponse(['success' => false, 'message' => 'Online payment is not configured. Please contact the parlour.'], 503);
    }
    if (!function_exists('curl_init')) {
        error_log('Razorpay request failed: PHP cURL extension is unavailable.');
        paymentResponse(['success' => false, 'message' => 'Online payment is temporarily unavailable. Please contact the parlour.'], 503);
    }

    $curl = curl_init('https://api.razorpay.com/v1/' . ltrim($path, '/'));
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_USERPWD => $keyId . ':' . $keySecret,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ];
    if ($payload !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($body === false) {
        error_log('Razorpay request failed: ' . $curlError);
        paymentResponse(['success' => false, 'message' => 'Online payment is temporarily unavailable. Please try again later.'], 502);
    }

    $decoded = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($decoded)) {
        error_log('Razorpay API returned HTTP ' . $status . '.');
        paymentResponse(['success' => false, 'message' => 'Online payment could not be started. Please contact the parlour.'], 502);
    }
    return $decoded;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    paymentResponse(['success' => false, 'message' => 'This endpoint only accepts payment requests.'], 405);
}
if (!verifyCSRFToken(paymentPostString('csrf_token'))) {
    paymentResponse(['success' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 403);
}

$action = paymentPostString('action');
$bookingId = trim(paymentPostString('booking_id'));
$sessionBookingId = (string)($_SESSION['booking_confirmed']['booking_id'] ?? '');
if ($bookingId === '' || $sessionBookingId === '' || !hash_equals($sessionBookingId, $bookingId)) {
    paymentResponse(['success' => false, 'message' => 'The booking could not be verified. Please refresh and try again.'], 403);
}

if ($action === 'create_order') {
    $stmt = $pdo->prepare(
        'SELECT p.id AS payment_id, p.amount, p.razorpay_order_id, a.booking_id,
                c.name AS customer_name, c.email, c.mobile, s.name AS service_name
         FROM payments p
         INNER JOIN appointments a ON a.id = p.appointment_id
         INNER JOIN customers c ON c.id = a.customer_id
         INNER JOIN services s ON s.id = a.service_id
         WHERE a.booking_id = ? AND p.method = ?
         LIMIT 1'
    );
    $stmt->execute([$bookingId, 'razorpay']);
    $payment = $stmt->fetch();
    if (!$payment) {
        paymentResponse(['success' => false, 'message' => 'No online payment is associated with this booking.'], 404);
    }

    $amountPaise = (int)round((float)$payment['amount'] * 100);
    if ($amountPaise < 100) {
        paymentResponse(['success' => false, 'message' => 'Please contact the parlour to confirm the service price before paying online.'], 422);
    }

    $keyId = getenv('RAZORPAY_KEY_ID');
    if (!$keyId || !getenv('RAZORPAY_KEY_SECRET')) {
        paymentResponse(['success' => false, 'message' => 'Online payment is not configured. Please contact the parlour.'], 503);
    }

    $orderId = (string)($payment['razorpay_order_id'] ?? '');
    if ($orderId === '') {
        $order = razorpayRequest('POST', 'orders', [
            'amount' => $amountPaise,
            'currency' => 'INR',
            'receipt' => $bookingId,
            'notes' => ['booking_id' => $bookingId],
        ]);
        $orderId = (string)($order['id'] ?? '');
        if ($orderId === '' || (int)($order['amount'] ?? 0) !== $amountPaise) {
            error_log('Razorpay order response was missing the expected order ID or amount.');
            paymentResponse(['success' => false, 'message' => 'Online payment could not be started. Please contact the parlour.'], 502);
        }
        $update = $pdo->prepare(
            "UPDATE payments SET razorpay_order_id = ?
             WHERE id = ? AND method = 'razorpay' AND status = 'pending'"
        );
        $update->execute([$orderId, $payment['payment_id']]);
        if ($update->rowCount() !== 1) {
            paymentResponse(['success' => false, 'message' => 'The payment session could not be saved. Please contact the parlour.'], 409);
        }
    }

    paymentResponse([
        'success' => true,
        'key_id' => $keyId,
        'order_id' => $orderId,
        'amount' => $amountPaise,
        'currency' => 'INR',
        'name' => 'Kanak Beauty Parlour',
        'description' => $payment['service_name'] . ' appointment',
        'booking_id' => $bookingId,
        'prefill' => [
            'name' => $payment['customer_name'],
            'email' => $payment['email'] ?? '',
            'contact' => $payment['mobile'],
        ],
    ]);
}

if ($action === 'verify_payment') {
    $paymentId = trim(paymentPostString('razorpay_payment_id'));
    $orderId = trim(paymentPostString('razorpay_order_id'));
    $signature = trim(paymentPostString('razorpay_signature'));
    if ($paymentId === '' || $orderId === '' || $signature === '') {
        paymentResponse(['success' => false, 'message' => 'Payment verification details are incomplete.'], 422);
    }

    $stmt = $pdo->prepare(
        "SELECT p.id, p.amount, p.status, p.razorpay_order_id
         FROM payments p
         INNER JOIN appointments a ON a.id = p.appointment_id
         WHERE a.booking_id = ? AND p.method = 'razorpay'
         LIMIT 1"
    );
    $stmt->execute([$bookingId]);
    $payment = $stmt->fetch();
    if (!$payment || !hash_equals((string)$payment['razorpay_order_id'], $orderId)) {
        paymentResponse(['success' => false, 'message' => 'Payment details did not match this booking.'], 422);
    }

    $keySecret = getenv('RAZORPAY_KEY_SECRET');
    if (!$keySecret) {
        paymentResponse(['success' => false, 'message' => 'Online payment verification is not configured.'], 503);
    }
    $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
    if (!hash_equals($expectedSignature, $signature)) {
        paymentResponse(['success' => false, 'message' => 'Payment verification failed. Please contact the parlour.'], 422);
    }

    $providerPayment = razorpayRequest('GET', 'payments/' . rawurlencode($paymentId));
    $expectedAmount = (int)round((float)$payment['amount'] * 100);
    if (
        ($providerPayment['order_id'] ?? '') !== $orderId
        || (int)($providerPayment['amount'] ?? 0) !== $expectedAmount
        || ($providerPayment['currency'] ?? '') !== 'INR'
        || ($providerPayment['status'] ?? '') !== 'captured'
    ) {
        paymentResponse(['success' => false, 'message' => 'The payment has not been captured. Please contact the parlour if money was deducted.'], 422);
    }

    if ($payment['status'] !== 'successful') {
        $update = $pdo->prepare(
            "UPDATE payments
             SET status = 'successful', razorpay_payment_id = ?, razorpay_signature = ?,
                 transaction_id = ?, paid_at = NOW()
             WHERE id = ? AND razorpay_order_id = ? AND status = 'pending'"
        );
        $update->execute([$paymentId, $signature, $paymentId, $payment['id'], $orderId]);
        if ($update->rowCount() !== 1) {
            paymentResponse(['success' => false, 'message' => 'Payment status could not be saved. Please contact the parlour.'], 409);
        }
    }

    $_SESSION['booking_confirmed']['payment_status'] = 'successful';
    paymentResponse(['success' => true, 'message' => 'Payment verified.']);
}

paymentResponse(['success' => false, 'message' => 'Unsupported payment action.'], 400);
