<?php
/**
 * DBell Creations — SMTP Contact Form Handler
 *
 * Uses PHPMailer for reliable SMTP delivery.
 * Zero CRM/database logic — emails only.
 *
 * Config: copy .env.example to .env and fill in your SMTP credentials.
 * Requires: vendor/autoload.php (composer require phpmailer/phpmailer)
 */

declare(strict_types=1);
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require __DIR__ . '/vendor/autoload.php';

// ── Configuration ──────────────────────────────────────────────────────────
const ADMIN_EMAIL     = 'dbellcreations@gmail.com';
const SITE_NAME       = 'DBell Creations';
const SITE_URL        = 'https://www.dbellcreations.com';
const RATE_LIMIT_SECS = 90;

$allowedOrigins = [SITE_URL, 'https://dbellcreations.com'];

// ── Helpers ────────────────────────────────────────────────────────────────
function envVal(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    $envFile = __DIR__ . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            $cache[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
        }
    }
    return $cache[$key] ?? $default;
}

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
if ($isAjax) header('Content-Type: application/json');

function respond(string $type, string $message, bool $isAjax): never {
    if ($isAjax) {
        echo json_encode(['type' => $type, 'message' => $message]);
    } elseif ($type === 'success') {
        header('Location: contact.html?success=1');
    } else {
        echo '<p style="color:red;font-family:sans-serif;">' . htmlspecialchars($message) . '</p>'
           . '<p><a href="contact.html">&larr; Go back</a></p>';
    }
    exit;
}

// ── Method guard ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('danger', 'Invalid request.', $isAjax);
}

// ── Origin check ───────────────────────────────────────────────────────────
$origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if ($origin !== '') {
    $allowed = false;
    foreach ($allowedOrigins as $o) {
        if ($origin === $o || str_starts_with($referer, $o)) { $allowed = true; break; }
    }
    if (!$allowed) respond('danger', 'Request origin not allowed.', $isAjax);
}

// ── Honeypot ───────────────────────────────────────────────────────────────
if (!empty($_POST['websiteUrl']) || !empty($_POST['website_url'])) {
    respond('success', "Thank you! We\u2019ll be in touch within 24 hours.", $isAjax);
}

// ── Rate limiting ──────────────────────────────────────────────────────────
$rateKey = 'mailer_' . md5($_SERVER['REMOTE_ADDR'] ?? 'x');
if (isset($_SESSION[$rateKey]) && (time() - $_SESSION[$rateKey]) < RATE_LIMIT_SECS) {
    respond('danger', 'Please wait a moment before submitting again.', $isAjax);
}

// ── Timing check ──────────────────────────────────────────────────────────
$startedAt = (int) ($_POST['formStartedAt'] ?? 0);
if ($startedAt > 0) {
    $elapsed = time() - $startedAt;
    if ($elapsed < 3 || $elapsed > 7200) {
        respond('danger', 'Submission timing invalid. Please refresh and try again.', $isAjax);
    }
}

// ── Sanitise + dual field-name support ────────────────────────────────────
function field(string ...$keys): string {
    foreach ($keys as $k) {
        $v = trim(strip_tags((string) ($_POST[$k] ?? '')));
        if ($v !== '') return $v;
    }
    return '';
}

$name      = field('name', 'contactName');
$email     = field('email', 'contactEmail');
$phone     = field('phone', 'contactPhone');
$company   = field('company', 'business_name', 'businessName');
$service   = field('service', 'service_interest', 'contactSubject');
$message   = field('message', 'contactMessage');
$source    = field('source_page') ?: 'contact';

// ── Validation ─────────────────────────────────────────────────────────────
$errors = [];
if ($name    === '')  $errors[] = 'Name is required.';
if ($email   === '')  $errors[] = 'Email is required.';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if ($message === '' || mb_strlen($message) < 10) $errors[] = 'Please include a message (at least 10 characters).';

if ($errors) respond('danger', implode(' ', $errors), $isAjax);

// ── Lock rate limit after successful validation ────────────────────────────
$_SESSION[$rateKey] = time();

// ── Build email content ────────────────────────────────────────────────────
$timestamp = date('D, M j Y \a\t g:i A T');
$safeName    = htmlspecialchars($name,    ENT_QUOTES | ENT_HTML5);
$safeEmail   = htmlspecialchars($email,   ENT_QUOTES | ENT_HTML5);
$safePhone   = htmlspecialchars($phone    ?: 'Not provided', ENT_QUOTES | ENT_HTML5);
$safeCompany = htmlspecialchars($company  ?: 'Not provided', ENT_QUOTES | ENT_HTML5);
$safeService = htmlspecialchars($service  ?: 'Not specified', ENT_QUOTES | ENT_HTML5);
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_HTML5));
$safeSource  = htmlspecialchars($source,  ENT_QUOTES | ENT_HTML5);

$adminSubject = "New Lead: {$name}" . ($company ? " ({$company})" : '');

$adminHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f8;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f8;padding:30px 0;">
    <tr><td>
      <table width="600" align="center" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:#6222CC;padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">New Lead — DBell Creations</h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:14px;">{$timestamp}</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:28px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Name</strong>
                  <span style="color:#111;font-size:15px;">{$safeName}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Email</strong>
                  <a href="mailto:{$safeEmail}" style="color:#6222CC;font-size:15px;">{$safeEmail}</a>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Phone</strong>
                  <span style="color:#111;font-size:15px;">{$safePhone}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Business</strong>
                  <span style="color:#111;font-size:15px;">{$safeCompany}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Service Needed</strong>
                  <span style="color:#111;font-size:15px;">{$safeService}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:10px 0;border-bottom:1px solid #eee;">
                  <strong style="color:#555;display:inline-block;width:130px;font-size:13px;">Source Page</strong>
                  <span style="color:#111;font-size:15px;">{$safeSource}</span>
                </td>
              </tr>
            </table>

            <!-- Message -->
            <h3 style="color:#6222CC;font-size:16px;margin:24px 0 10px;">Message</h3>
            <div style="background:#f9f7fd;border-left:4px solid #6222CC;border-radius:4px;padding:16px 20px;color:#333;font-size:15px;line-height:1.6;">
              {$safeMessage}
            </div>

            <!-- Quick actions -->
            <div style="margin-top:28px;text-align:center;">
              <a href="mailto:{$safeEmail}" style="display:inline-block;background:#6222CC;color:#fff;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:700;font-size:14px;margin:4px;">Reply to {$safeName}</a>
              <a href="tel:{$safePhone}" style="display:inline-block;background:#FBA504;color:#fff;text-decoration:none;padding:12px 28px;border-radius:50px;font-weight:700;font-size:14px;margin:4px;">Call Now</a>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f9f9f9;padding:16px 32px;text-align:center;border-top:1px solid #eee;">
            <p style="margin:0;color:#999;font-size:12px;">DBell Creations &middot; Fairhope, AL &middot; <a href="https://www.dbellcreations.com" style="color:#6222CC;">dbellcreations.com</a></p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

$adminText = "NEW LEAD — DBell Creations\n{$timestamp}\n\n"
    . "Name:    {$name}\nEmail:   {$email}\nPhone:   {$phone}\n"
    . "Company: {$company}\nService: {$service}\nSource:  {$source}\n\n"
    . "Message:\n{$message}\n";

// ── Auto-reply to visitor ──────────────────────────────────────────────────
$confirmSubject = "Got your message, {$name} — DBell Creations";

$confirmHtml = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f4f8;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f8;padding:30px 0;">
    <tr><td>
      <table width="600" align="center" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;max-width:600px;width:100%;">

        <tr>
          <td style="background:#6222CC;padding:28px 32px;">
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">We got your message!</h1>
          </td>
        </tr>

        <tr>
          <td style="padding:32px;">
            <p style="font-size:16px;color:#333;margin-top:0;">Hi {$safeName},</p>
            <p style="font-size:15px;color:#444;line-height:1.6;">Thanks for reaching out to DBell Creations. I received your message and will get back to you within <strong>24 hours</strong> — typically the same day.</p>
            <p style="font-size:15px;color:#444;line-height:1.6;">While you wait, here are a few things that might be helpful:</p>

            <table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
              <tr>
                <td style="padding:12px 16px;background:#f9f7fd;border-left:4px solid #6222CC;border-radius:4px;margin-bottom:10px;">
                  <a href="https://www.dbellcreations.com/free-mockup.html" style="color:#6222CC;font-weight:700;text-decoration:none;font-size:14px;">&#10230; Request a free homepage mockup</a>
                </td>
              </tr>
              <tr><td style="padding:4px 0;"></td></tr>
              <tr>
                <td style="padding:12px 16px;background:#f9f7fd;border-left:4px solid #FBA504;border-radius:4px;">
                  <a href="https://www.dbellcreations.com/project.html" style="color:#FBA504;font-weight:700;text-decoration:none;font-size:14px;">&#10230; See our portfolio and case studies</a>
                </td>
              </tr>
              <tr><td style="padding:4px 0;"></td></tr>
              <tr>
                <td style="padding:12px 16px;background:#f9f7fd;border-left:4px solid #6222CC;border-radius:4px;">
                  <a href="https://www.dbellcreations.com/pricing.html" style="color:#6222CC;font-weight:700;text-decoration:none;font-size:14px;">&#10230; View our pricing</a>
                </td>
              </tr>
            </table>

            <p style="font-size:15px;color:#444;margin-bottom:4px;">Talk soon,</p>
            <p style="font-size:15px;color:#333;font-weight:700;margin-top:0;">DBell Creations</p>
            <p style="font-size:13px;color:#777;margin:0;">
              <a href="tel:2514062292" style="color:#6222CC;">251-406-2292</a> &nbsp;&middot;&nbsp;
              <a href="mailto:dbellcreations@gmail.com" style="color:#6222CC;">dbellcreations@gmail.com</a> &nbsp;&middot;&nbsp;
              <a href="https://www.dbellcreations.com" style="color:#6222CC;">dbellcreations.com</a>
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#f9f9f9;padding:14px 32px;text-align:center;border-top:1px solid #eee;">
            <p style="margin:0;color:#bbb;font-size:11px;">You received this because you submitted a contact form on dbellcreations.com.</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

$confirmText = "Hi {$name},\n\n"
    . "Thanks for contacting DBell Creations! I'll get back to you within 24 hours.\n\n"
    . "In the meantime:\n"
    . "- Request a free mockup: " . SITE_URL . "/free-mockup.html\n"
    . "- See our work: " . SITE_URL . "/project.html\n"
    . "- View pricing: " . SITE_URL . "/pricing.html\n\n"
    . "Talk soon,\nDBell Creations\n251-406-2292\ndbellcreations@gmail.com\n";

// ── PHPMailer send function ────────────────────────────────────────────────
function sendMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $textBody,
    string $replyToEmail,
    string $replyToName
): bool {
    $smtp = [
        'host'       => envVal('SMTP_HOST', ''),
        'port'       => (int) envVal('SMTP_PORT', '587'),
        'encryption' => strtolower(envVal('SMTP_ENCRYPTION', 'tls')),
        'user'       => envVal('SMTP_USER', ''),
        'pass'       => envVal('SMTP_PASS', ''),
        'from'       => envVal('MAIL_FROM', ADMIN_EMAIL),
        'fromName'   => envVal('MAIL_FROM_NAME', SITE_NAME),
    ];

    $mail = new PHPMailer(true);

    try {
        if (!empty($smtp['host']) && !empty($smtp['user'])) {
            // SMTP mode
            $mail->isSMTP();
            $mail->Host       = $smtp['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp['user'];
            $mail->Password   = $smtp['pass'];
            $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp['port'];
        }
        // Falls back to PHP mail() if no SMTP config

        $mail->setFrom($smtp['from'], $smtp['fromName']);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($replyToEmail, $replyToName);

        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Subject  = $subject;
        $mail->Body     = $htmlBody;
        $mail->AltBody  = $textBody;

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('DBell mailer.php PHPMailer error: ' . $mail->ErrorInfo . ' | ' . $e->getMessage());
        return false;
    }
}

// ── Send admin notification ────────────────────────────────────────────────
$adminSent = sendMail(
    toEmail:      ADMIN_EMAIL,
    toName:       SITE_NAME,
    subject:      $adminSubject,
    htmlBody:     $adminHtml,
    textBody:     $adminText,
    replyToEmail: $email,
    replyToName:  $name
);

// ── Send visitor auto-reply ────────────────────────────────────────────────
sendMail(
    toEmail:      $email,
    toName:       $name,
    subject:      $confirmSubject,
    htmlBody:     $confirmHtml,
    textBody:     $confirmText,
    replyToEmail: ADMIN_EMAIL,
    replyToName:  SITE_NAME
);

if (!$adminSent) {
    error_log("DBell mailer.php: admin send failed for {$email} — " . date('Y-m-d H:i:s T'));
}

// ── Non-AJAX redirect ─────────────────────────────────────────────────────
$redirect = trim(strip_tags($_POST['redirectSuccess'] ?? ''));
if (!$isAjax && $redirect && preg_match('/^[\w\-\.\/]+\.html(\?[\w=&\-]+)?$/', $redirect)) {
    header("Location: {$redirect}");
    exit;
}

respond('success', "Thank you, {$name}! Your message has been received. We\u2019ll be in touch within 24 hours.", $isAjax);
