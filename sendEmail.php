<?php

// Replace this with your own inbox address
$siteOwnersEmail = 'dbellcreations@gmail.com';

function envFileValue(string $key, ?string $filePath = null): ?string {
    $candidates = [];

    if ($filePath) {
        $candidates[] = $filePath;
    }

    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . '.env';
    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'WebsiteScan' . DIRECTORY_SEPARATOR . '.env';

    foreach ($candidates as $candidate) {
        if (!is_file($candidate) || !is_readable($candidate)) {
            continue;
        }

        $lines = @file($candidate, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            continue;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false || $line[0] === '#') {
                continue;
            }

            [$envKey, $envValue] = array_pad(explode('=', $line, 2), 2, '');
            if (trim($envKey) !== $key) {
                continue;
            }

            return trim($envValue, " \t\n\r\0\x0B\"'");
        }
    }

    return null;
}

function mailConfig(): array {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [
        'smtp_host' => envFileValue('SMTP_HOST') ?: '',
        'smtp_port' => (int) (envFileValue('SMTP_PORT') ?: 587),
        'smtp_user' => envFileValue('SMTP_USER') ?: '',
        'smtp_pass' => envFileValue('SMTP_PASS') ?: '',
        'smtp_encryption' => strtolower((string) (envFileValue('SMTP_ENCRYPTION') ?: 'tls')),
        'mail_from' => envFileValue('MAIL_FROM') ?: '',
        'mail_from_name' => envFileValue('MAIL_FROM_NAME') ?: 'DBell Creations',
    ];

    return $config;
}

function mailTransportFromEmail(): string {
    $config = mailConfig();

    if (!empty($config['mail_from']) && filter_var($config['mail_from'], FILTER_VALIDATE_EMAIL)) {
        return $config['mail_from'];
    }

    if (!empty($config['smtp_user']) && filter_var($config['smtp_user'], FILTER_VALIDATE_EMAIL)) {
        return $config['smtp_user'];
    }

    global $siteOwnersEmail;
    return $siteOwnersEmail;
}

function mailTransportFromName(): string {
    $config = mailConfig();
    return trim((string) ($config['mail_from_name'] ?? '')) ?: 'DBell Creations';
}

function messageHostName(): string {
    $host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    $host = preg_replace('/:\d+$/', '', (string) $host);
    $host = preg_replace('/[^A-Za-z0-9\.\-]/', '', (string) $host);
    return $host !== '' ? $host : 'localhost';
}

function normalizeHeaderText(string $value): string {
    return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

function encodeHeaderValue(string $value): string {
    $value = normalizeHeaderText($value);
    return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
}

function formatAddressHeader(string $email, string $name = ''): string {
    $safeEmail = normalizeHeaderText($email);
    $safeName = normalizeHeaderText($name);

    if ($safeName === '') {
        return $safeEmail;
    }

    return encodeHeaderValue($safeName) . ' <' . $safeEmail . '>';
}

function buildEmailHeaders(string $fromEmail, string $fromName, string $replyToEmail, string $replyToName = ''): array {
    $boundary = 'b1_' . md5(uniqid((string) mt_rand(), true));
    $headers = [
        'From: ' . formatAddressHeader($fromEmail, $fromName),
        'Reply-To: ' . formatAddressHeader($replyToEmail, $replyToName),
        'Date: ' . date('r'),
        'Message-ID: <' . md5(uniqid((string) mt_rand(), true)) . '@' . messageHostName() . '>',
        'MIME-Version: 1.0',
        'X-Mailer: DBellContact/2.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    return [$headers, $boundary];
}

function buildEmailBody(string $htmlBody, string $textBody, string $boundary): string {
    return "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $textBody . "\r\n\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $htmlBody . "\r\n\r\n"
        . "--{$boundary}--";
}

function smtpResponseOk(string $response, array $codes): bool {
    foreach ($codes as $code) {
        if (str_starts_with($response, (string) $code)) {
            return true;
        }
    }

    return false;
}

function smtpRead($socket): string {
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    return $response;
}

function smtpCommand($socket, string $command): string {
    fwrite($socket, $command . "\r\n");
    return smtpRead($socket);
}

function sendViaSmtp(string $to, string $subject, string $body, array $headers, string $fromEmail): bool {
    $config = mailConfig();
    $host = $config['smtp_host'] ?? '';
    $port = (int) ($config['smtp_port'] ?? 587);
    $user = $config['smtp_user'] ?? '';
    $pass = $config['smtp_pass'] ?? '';
    $encryption = strtolower((string) ($config['smtp_encryption'] ?? 'tls'));

    if ($host === '' || $user === '' || $pass === '' || $port <= 0) {
        return false;
    }

    $transportHost = ($encryption === 'ssl' ? 'ssl://' : '') . $host;
    $socket = @fsockopen($transportHost, $port, $errno, $errstr, 15);
    if (!$socket) {
        error_log('Contact form SMTP connection failed: ' . trim((string) $errstr));
        return false;
    }

    stream_set_timeout($socket, 20);

    $greeting = smtpRead($socket);
    if (!smtpResponseOk($greeting, [220])) {
        fclose($socket);
        return false;
    }

    $clientName = messageHostName();
    $ehlo = smtpCommand($socket, 'EHLO ' . $clientName);
    if (!smtpResponseOk($ehlo, [250])) {
        fclose($socket);
        return false;
    }

    if ($encryption === 'tls') {
        $startTls = smtpCommand($socket, 'STARTTLS');
        if (!smtpResponseOk($startTls, [220])) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }

        $ehlo = smtpCommand($socket, 'EHLO ' . $clientName);
        if (!smtpResponseOk($ehlo, [250])) {
            fclose($socket);
            return false;
        }
    }

    if (!smtpResponseOk(smtpCommand($socket, 'AUTH LOGIN'), [334])) {
        fclose($socket);
        return false;
    }

    if (!smtpResponseOk(smtpCommand($socket, base64_encode($user)), [334])) {
        fclose($socket);
        return false;
    }

    if (!smtpResponseOk(smtpCommand($socket, base64_encode($pass)), [235])) {
        fclose($socket);
        return false;
    }

    if (!smtpResponseOk(smtpCommand($socket, 'MAIL FROM: <' . $fromEmail . '>'), [250])) {
        fclose($socket);
        return false;
    }

    if (!smtpResponseOk(smtpCommand($socket, 'RCPT TO: <' . $to . '>'), [250, 251])) {
        fclose($socket);
        return false;
    }

    if (!smtpResponseOk(smtpCommand($socket, 'DATA'), [354])) {
        fclose($socket);
        return false;
    }

    $message = implode("\r\n", $headers)
        . "\r\nTo: <" . normalizeHeaderText($to) . ">\r\n"
        . 'Subject: ' . encodeHeaderValue($subject)
        . "\r\n\r\n"
        . preg_replace("/(?m)^\./", '..', $body)
        . "\r\n.\r\n";

    fwrite($socket, $message);
    $result = smtpRead($socket);
    smtpCommand($socket, 'QUIT');
    fclose($socket);

    return smtpResponseOk($result, [250]);
}

function sendContactEmail(string $to, string $subject, string $htmlBody, string $textBody, string $replyToEmail, string $replyToName = ''): bool {
    $fromEmail = mailTransportFromEmail();
    $fromName = mailTransportFromName();
    [$headers, $boundary] = buildEmailHeaders($fromEmail, $fromName, $replyToEmail, $replyToName);
    $body = buildEmailBody($htmlBody, $textBody, $boundary);

    if (sendViaSmtp($to, $subject, $body, $headers, $fromEmail)) {
        return true;
    }

    ini_set('sendmail_from', $fromEmail);

    return mail(
        $to,
        encodeHeaderValue($subject),
        $body,
        implode("\r\n", $headers),
        '-f' . $fromEmail
    );
}

function clientIpAddress() {
    $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $value = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }
    }
    return '0.0.0.0';
}

function rateLimitFilePath($ip) {
    $safeIp = preg_replace('/[^0-9a-fA-F:\.]/', '_', $ip);
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dbell_contact_' . md5($safeIp) . '.txt';
}

function isRateLimited($ip, $windowSeconds) {
    $file = rateLimitFilePath($ip);
    if (!file_exists($file)) {
        return false;
    }
    $last = (int) @file_get_contents($file);
    return ($last > 0 && (time() - $last) < $windowSeconds);
}

function updateRateLimit($ip) {
    $file = rateLimitFilePath($ip);
    @file_put_contents($file, (string) time(), LOCK_EX);
}

function safeRedirectPath($path) {
    if (!$path) {
        return null;
    }

    $path = trim($path);

    // Block full URLs and protocol-relative redirects.
    if (preg_match('/^https?:\/\//i', $path) || strpos($path, '//') === 0) {
        return null;
    }

    // Keep redirects local to HTML pages in this site.
    if (!preg_match('/^[a-zA-Z0-9_\-\.\/\?=&%]+$/', $path)) {
        return null;
    }

    return $path;
}

function appendQueryValue($path, $key, $value) {
    if (!$path) {
        return null;
    }

    $separator = (strpos($path, '?') !== false) ? '&' : '?';
    return $path . $separator . rawurlencode($key) . '=' . rawurlencode($value);
}

/**
 * Save a contact form submission to the WebsiteScan CRM database.
 * Silently fails if the DB is unavailable so email delivery is never blocked.
 */
function saveToCrm(string $name, string $email, string $phone, string $contactMessage, string $subject): void {
    try {
        $dbConfigFile = __DIR__ . '/WebsiteScan/config/database.php';
        if (!file_exists($dbConfigFile)) {
            return;
        }
        $dbConfig = require $dbConfigFile;
        if (empty($dbConfig['database'])) {
            return;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $dbConfig['host']     ?? '127.0.0.1',
            $dbConfig['port']     ?? 3306,
            $dbConfig['database']
        );
        $pdo = new PDO($dsn, $dbConfig['username'] ?? 'root', $dbConfig['password'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $now = date('Y-m-d H:i:s');

        // Find or create a lead record
        $leadId = null;
        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT id FROM leads WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            if ($existing) {
                $leadId = (int) $existing['id'];
                $pdo->prepare("UPDATE leads SET contact_name = ?, phone = ? WHERE id = ?")
                    ->execute([$name, $phone ?: null, $leadId]);
            }
        }

        if ($leadId === null) {
            $stmt = $pdo->prepare(
                "INSERT INTO leads (contact_name, email, phone, notes, source, status, created_at) VALUES (?, ?, ?, ?, 'contact_form', 'new', ?)"
            );
            $stmt->execute([$name, $email, $phone ?: null, $contactMessage, $now]);
            $leadId = (int) $pdo->lastInsertId();
        }

        // Check if contact_requests table has the new columns
        $hasSource = false;
        $hasStatus = false;
        $colStmt = $pdo->query("SHOW COLUMNS FROM `contact_requests`");
        foreach ($colStmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            if ($col['Field'] === 'source') $hasSource = true;
            if ($col['Field'] === 'status') $hasStatus = true;
        }

        if ($hasSource && $hasStatus) {
            $pdo->prepare("INSERT INTO contact_requests (lead_id, name, email, phone, message, service_type, source, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'contact_form', 'new', ?)")
                ->execute([$leadId, $name, $email, $phone ?: null, $contactMessage, $subject, $now]);
        } else {
            $pdo->prepare("INSERT INTO contact_requests (lead_id, name, email, phone, message, service_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$leadId, $name, $email, $phone ?: null, $contactMessage, $subject, $now]);
        }
    } catch (Throwable $e) {
        error_log('sendEmail.php CRM save error: ' . $e->getMessage());
    }
}

if ($_POST) {
    $error = array();

    $message = '';
    $name = trim(stripslashes($_POST['contactName']));
    $email = trim(stripslashes($_POST['contactEmail']));
    $phone = trim(stripslashes($_POST['contactPhone'] ?? ''));
    $subject = trim(stripslashes($_POST['contactSubject']));
    $contact_message = trim(stripslashes($_POST['contactMessage']));
    $honeypot = trim($_POST['websiteUrl'] ?? '');
    $formStartedAt = (int) ($_POST['formStartedAt'] ?? 0);
    $redirectSuccess = safeRedirectPath($_POST['redirectSuccess'] ?? null);
    $redirectError = safeRedirectPath($_POST['redirectError'] ?? null);
    $ip = clientIpAddress();

    // Honeypot should stay blank for real users.
    if ($honeypot !== '') {
        $error['spam'] = 'Spam detected.';
    }

    // Humans typically take at least 3 seconds to fill out a form.
    // We only verify the timestamp was set by JS (non-zero, within 2 hours).
    $nowMs = (int) floor(microtime(true) * 1000);
    $elapsedMs = $nowMs - $formStartedAt;
    if ($formStartedAt <= 0 || $elapsedMs < 3000 || $elapsedMs > 7200000) {
        $error['timing'] = 'Submission too fast.';
    }

    // Basic per-IP rate limit: 1 submission every 60 seconds.
    if (isRateLimited($ip, 60)) {
        $error['rate'] = 'Please wait a minute before submitting again.';
    }

    if (strlen($name) < 2) {
        $error['name'] = 'Please enter your name.';
    }

    if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is', $email)) {
        $error['email'] = 'Please enter a valid email address.';
    }

    if (strlen($contact_message) < 15) {
        $error['message'] = 'Please enter your message. It should have at least 15 characters.';
    }

    if ($subject === '') {
        $subject = 'Contact Form Submission';
    }

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($contact_message, ENT_QUOTES, 'UTF-8'));

    $message .= '<h2>New Contact Form Submission</h2>';
    $message .= '<p><strong>Name:</strong> ' . $safeName . '</p>';
    $message .= '<p><strong>Email:</strong> ' . $safeEmail . '</p>';
    if ($safePhone !== '') {
        $message .= '<p><strong>Phone:</strong> ' . $safePhone . '</p>';
    }
    $message .= '<p><strong>Subject:</strong> ' . $safeSubject . '</p>';
    $message .= '<p><strong>Message:</strong><br>' . $safeMessage . '</p>';
    $message .= '<hr><p>This email was sent from the DBell Creations contact form.</p>';

    $textMessage = "New Contact Form Submission\n\n";
    $textMessage .= "Name: {$name}\n";
    $textMessage .= "Email: {$email}\n";
    if ($phone !== '') {
        $textMessage .= "Phone: {$phone}\n";
    }
    $textMessage .= "Subject: {$subject}\n\n";
    $textMessage .= "Message:\n{$contact_message}\n\n";
    $textMessage .= "This email was sent from the DBell Creations contact form.\n";

    $replyToEmail = $email;
    if (!filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
        $replyToEmail = mailTransportFromEmail();
    }

    if (empty($error)) {
        // Always save to CRM regardless of email outcome
        saveToCrm($name, $email, $phone, $contact_message, $subject);

        $mail = sendContactEmail($siteOwnersEmail, $subject, $message, $textMessage, $replyToEmail, $name);

        if ($mail) {
            updateRateLimit($ip);
            if ($redirectSuccess) {
                header('Location: ' . appendQueryValue($redirectSuccess, 'mail', 'sent'));
                exit;
            }
            echo 'OK';
        } else {
            if ($redirectError) {
                header('Location: ' . appendQueryValue($redirectError, 'mail', 'failed'));
                exit;
            }
            echo 'Something went wrong. Please try again.';
        }
    } else {
        $response = (isset($error['name'])) ? $error['name'] . "<br /> \n" : null;
        $response .= (isset($error['email'])) ? $error['email'] . "<br /> \n" : null;
        $response .= (isset($error['message'])) ? $error['message'] . '<br />' : null;
        $response .= (isset($error['rate'])) ? $error['rate'] . '<br />' : null;

        if ($redirectError) {
            header('Location: ' . appendQueryValue($redirectError, 'mail', 'invalid'));
            exit;
        }

        echo $response;
    }
}

?>
