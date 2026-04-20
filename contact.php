<?php
/**
 * DBell Creations — Contact Form Handler
 * Sends HTML email to admin and plain confirmation to the lead.
 *
 * Accepts both field-name conventions:
 *   Modern:  name, email, phone, message, service_interest
 *   Legacy:  contactName, contactEmail, contactPhone, contactMessage
 */

session_start();

$adminEmail = 'dbellcreations@gmail.com';
$allowedOrigins = ['https://www.dbellcreations.com', 'https://dbellcreations.com'];

// ── AJAX detection ─────────────────────────────────────────────────────────
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json');
}

function respond(array $data, bool $isAjax): void {
    if ($isAjax) {
        echo json_encode($data);
    } else {
        if ($data['type'] === 'success') {
            header('Location: contact.html?success=1');
        } else {
            echo '<p style="color:red;font-family:sans-serif;">' . htmlspecialchars($data['message']) . '</p>';
            echo '<p><a href="contact.html">&larr; Go back</a></p>';
        }
    }
    exit;
}

// ── Only accept POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['type' => 'danger', 'message' => 'Invalid request.'], $isAjax);
}

// ── Origin / Referer check ─────────────────────────────────────────────────
$origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$originOk = false;
foreach ($allowedOrigins as $allowed) {
    if ($origin === $allowed || str_starts_with($referer, $allowed)) {
        $originOk = true;
        break;
    }
}
// Allow local dev (empty origin/referer) and CLI testing
if (!$originOk && $origin !== '') {
    respond(['type' => 'danger', 'message' => 'Request origin not allowed.'], $isAjax);
}

// ── Honeypot checks ────────────────────────────────────────────────────────
// Silently succeed to confuse bots — don't reveal the check
if (!empty($_POST['websiteUrl']) || !empty($_POST['website_url'])) {
    respond(['type' => 'success', 'message' => "Thank you! We'll be in touch within 24 hours."], $isAjax);
}

// ── Rate limiting (1 submission per 90 s per IP) ───────────────────────────
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey = 'last_submit_' . md5($ip);
if (isset($_SESSION[$rateKey]) && (time() - $_SESSION[$rateKey]) < 90) {
    respond(['type' => 'danger', 'message' => 'Please wait a moment before submitting again.'], $isAjax);
}

// ── Timing check (minimum 3 s, max 7200 s fill time) ──────────────────────
$startedAt = (int) ($_POST['formStartedAt'] ?? 0);
if ($startedAt > 0) {
    $elapsed = time() - $startedAt;
    if ($elapsed < 3 || $elapsed > 7200) {
        respond(['type' => 'danger', 'message' => 'Form submission timing invalid. Please try again.'], $isAjax);
    }
}

// ── Sanitise inputs (support both naming conventions) ─────────────────────
function sp(string $key, string $fallback = ''): string {
    $alt = [
        'name'             => 'contactName',
        'email'            => 'contactEmail',
        'phone'            => 'contactPhone',
        'message'          => 'contactMessage',
        'service_interest' => 'contactSubject',
    ];
    $val = $_POST[$key] ?? $_POST[$alt[$key] ?? ''] ?? $fallback;
    return trim(strip_tags((string) $val));
}

$name            = sp('name');
$email           = sp('email');
$phone           = sp('phone');
$businessName    = trim(strip_tags($_POST['business_name'] ?? ''));
$message         = sp('message');
$serviceInterest = sp('service_interest');
$sourcePage      = trim(strip_tags($_POST['source_page'] ?? 'contact'));

// ── Validation ─────────────────────────────────────────────────────────────
$errors = [];
if ($name    === '')  $errors[] = 'Name is required.';
if ($email   === '')  $errors[] = 'Email is required.';
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($message === '')  $errors[] = 'Message is required.';

if ($errors) {
    respond(['type' => 'danger', 'message' => implode(' ', $errors)], $isAjax);
}

// ── Record rate-limit timestamp after validation ───────────────────────────
$_SESSION[$rateKey] = time();

// ── Build emails ───────────────────────────────────────────────────────────
$safeN  = htmlspecialchars($name,            ENT_QUOTES);
$safeE  = htmlspecialchars($email,           ENT_QUOTES);
$safeP  = htmlspecialchars($phone ?: 'Not provided', ENT_QUOTES);
$safeBN = htmlspecialchars($businessName ?: 'Not provided', ENT_QUOTES);
$safeSI = htmlspecialchars($serviceInterest ?: 'Not specified', ENT_QUOTES);
$safeSP = htmlspecialchars($sourcePage,      ENT_QUOTES);
$safeM  = nl2br(htmlspecialchars($message,   ENT_QUOTES));
$time   = date('Y-m-d H:i:s T');

$adminSubject = "New Lead: {$name}" . ($businessName ? " ({$businessName})" : '');

$adminHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
  <h2 style="color:#6222CC;margin-bottom:4px;">New Lead — DBell Creations</h2>
  <p style="color:#888;margin-top:0;font-size:13px;">{$time}</p>
  <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;width:150px;">Name</td><td style="padding:8px 10px;border-bottom:1px solid #eee;">{$safeN}</td></tr>
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;">Email</td><td style="padding:8px 10px;border-bottom:1px solid #eee;"><a href="mailto:{$safeE}" style="color:#6222CC;">{$safeE}</a></td></tr>
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;">Phone</td><td style="padding:8px 10px;border-bottom:1px solid #eee;">{$safeP}</td></tr>
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;">Business</td><td style="padding:8px 10px;border-bottom:1px solid #eee;">{$safeBN}</td></tr>
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;">Service</td><td style="padding:8px 10px;border-bottom:1px solid #eee;">{$safeSI}</td></tr>
    <tr><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:bold;">Source Page</td><td style="padding:8px 10px;border-bottom:1px solid #eee;">{$safeSP}</td></tr>
  </table>
  <h3 style="color:#6222CC;">Message</h3>
  <p style="background:#f9f9f9;padding:16px;border-left:4px solid #6222CC;border-radius:4px;margin:0;">{$safeM}</p>
</body>
</html>
HTML;

$adminText = "New Lead: {$name}" . ($businessName ? " ({$businessName})" : '') . "\n"
           . "Email: {$email}\nPhone: {$phone}\nService: {$serviceInterest}\n"
           . "Source: {$sourcePage}\nMessage: {$message}\nTime: {$time}\n";

$confirmSubject = "Thanks for reaching out, {$name}! — DBell Creations";

$confirmHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;color:#333;">
  <h2 style="color:#6222CC;">We got your message, {$safeN}!</h2>
  <p>Thanks for contacting DBell Creations. I'll get back to you within <strong>24 hours</strong> — usually the same day.</p>
  <p>While you wait:</p>
  <ul>
    <li><a href="https://www.dbellcreations.com/free-mockup.html" style="color:#6222CC;">Request a free homepage mockup</a></li>
    <li><a href="https://www.dbellcreations.com/project.html" style="color:#6222CC;">See our portfolio</a></li>
    <li><a href="https://www.dbellcreations.com/pricing.html" style="color:#6222CC;">View our pricing</a></li>
  </ul>
  <p style="margin-top:24px;">Talk soon,<br>
  <strong>DBell Creations</strong><br>
  <a href="tel:2514062292" style="color:#6222CC;">251-406-2292</a> &nbsp;|&nbsp;
  <a href="mailto:dbellcreations@gmail.com" style="color:#6222CC;">dbellcreations@gmail.com</a></p>
</body>
</html>
HTML;

$confirmText = "Hi {$name},\n\nThanks for contacting DBell Creations! "
             . "I'll get back to you within 24 hours.\n\n"
             . "Talk soon,\nDBell Creations\n251-406-2292\ndbellcreations@gmail.com\n";

// ── Send via MIME multipart ────────────────────────────────────────────────
function buildMimeEmail(string $textPart, string $htmlPart, string $from, string $replyTo): array {
    $b = md5(uniqid('', true));
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$b}\"\r\n";
    $headers .= "From: DBell Creations <{$from}>\r\n";
    $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $body  = "--{$b}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$textPart}\r\n";
    $body .= "--{$b}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$htmlPart}\r\n";
    $body .= "--{$b}--";

    return [$headers, $body];
}

[$aH, $aB] = buildMimeEmail($adminText,   $adminHtml,   $adminEmail, "{$name} <{$email}>");
[$cH, $cB] = buildMimeEmail($confirmText, $confirmHtml, $adminEmail, $adminEmail);

$adminSent = mail($adminEmail, $adminSubject,   $aB, $aH);
             mail($email,      $confirmSubject, $cB, $cH);

if (!$adminSent) {
    error_log("DBell contact.php: admin mail failed — from {$email} at {$time}");
}

// ── Non-AJAX success redirect ──────────────────────────────────────────────
$redirectSuccess = trim(strip_tags($_POST['redirectSuccess'] ?? ''));
if (!$isAjax && $redirectSuccess) {
    // Validate redirect stays on same domain
    if (preg_match('/^[a-zA-Z0-9_\-\.\/]+\.html(\?[a-zA-Z0-9=&_\-]+)?$/', $redirectSuccess)) {
        header("Location: {$redirectSuccess}");
        exit;
    }
}

respond([
    'type'    => 'success',
    'message' => "Thank you, {$name}! Your message has been received. We'll be in touch within 24 hours.",
], $isAjax);
