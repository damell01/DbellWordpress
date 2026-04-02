<?php
namespace App\Services;

use App\Models\Setting;

class MailService {
    private array $config;
    private array $settings = [];
    private string $lastError = '';

    public function __construct() {
        $this->config = require base_path('config/mail.php');

        try {
            $settingModel = new Setting();
            $this->settings = $settingModel->getAll();

            $overrides = [
                'driver' => $this->settings['mail_driver'] ?? '',
                'from' => $this->settings['mail_from'] ?? '',
                'from_name' => $this->settings['mail_from_name'] ?? '',
                'smtp_host' => $this->settings['smtp_host'] ?? '',
                'smtp_port' => $this->settings['smtp_port'] ?? '',
                'smtp_user' => $this->settings['smtp_user'] ?? '',
                'smtp_pass' => $this->settings['smtp_pass'] ?? '',
                'encryption' => $this->settings['smtp_encryption'] ?? '',
                'admin_email' => $this->settings['admin_email'] ?? '',
            ];

            foreach ($overrides as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $this->config[$key] = $value;
                }
            }

            if (!empty($this->config['smtp_port'])) {
                $this->config['smtp_port'] = (int) $this->config['smtp_port'];
            }
        } catch (\Throwable $e) {
            $this->settings = [];
        }
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = '', ?string $replyToEmail = null, string $replyToName = ''): bool {
        $this->lastError = '';

        $fromEmail = $this->effectiveFromEmail();
        if ($fromEmail === '') {
            $this->lastError = 'Mail sender address is missing. Set From Email first.';
            return false;
        }

        $driver = strtolower((string) ($this->config['driver'] ?? 'mail'));

        if ($driver === 'smtp' && !empty($this->config['smtp_host'])) {
            return $this->sendSmtp($to, $subject, $htmlBody, $textBody, $replyToEmail, $replyToName);
        }

        return $this->sendNative($to, $subject, $htmlBody, $textBody, $replyToEmail, $replyToName);
    }

    public function getLastError(): string {
        return $this->lastError;
    }

    public function sendTestEmail(string $to): bool {
        $appName = config('app.name', 'VerityScan');
        $driver = strtoupper((string) ($this->config['driver'] ?? 'mail'));
        $host = (string) ($this->config['smtp_host'] ?? '');
        $port = (string) ($this->config['smtp_port'] ?? '');
        $encryption = (string) ($this->config['encryption'] ?? '');
        $time = date('Y-m-d H:i:s');
        $signatureHtml = $this->signatureHtml();
        $signatureText = $this->signatureText();

        $html = "<h2>{$this->escape($appName)} Test Email</h2>"
            . "<p>This is a test email from your WebsiteScan admin settings.</p>"
            . "<p><strong>Sent at:</strong> {$this->escape($time)}</p>"
            . "<p><strong>Driver:</strong> {$this->escape($driver)}</p>"
            . ($host !== '' ? "<p><strong>SMTP Host:</strong> " . $this->escape($host) . "</p>" : '')
            . ($port !== '' ? "<p><strong>SMTP Port:</strong> " . $this->escape($port) . "</p>" : '')
            . ($encryption !== '' ? "<p><strong>Encryption:</strong> " . $this->escape($encryption) . "</p>" : '')
            . "<p>If you received this email, outgoing mail is working.</p>"
            . $signatureHtml;

        $text = "{$appName} test email\n"
            . "Sent at: {$time}\n"
            . "Driver: {$driver}\n"
            . ($host !== '' ? "SMTP Host: {$host}\n" : '')
            . ($port !== '' ? "SMTP Port: {$port}\n" : '')
            . ($encryption !== '' ? "Encryption: {$encryption}\n" : '')
            . "\nIf you received this email, outgoing mail is working.\n"
            . $signatureText;

        return $this->send($to, "[{$appName}] Test Email", $html, $text, $this->supportEmail(), $this->supportName());
    }

    private function sendNative(string $to, string $subject, string $html, string $text, ?string $replyToEmail, string $replyToName): bool {
        $fromName = $this->safeHeaderName((string) ($this->config['from_name'] ?? 'VerityScan'));
        $fromEmail = $this->effectiveFromEmail();
        $replyTo = $this->normalizeAddress((string) ($replyToEmail ?: $fromEmail));
        $boundary = 'b1_' . md5(uniqid((string) mt_rand(), true));
        $messageId = '<' . md5(uniqid((string) mt_rand(), true)) . '@' . $this->messageHostName() . '>';

        $headers = [];
        $headers[] = 'From: ' . $this->formatAddressHeader($fromEmail, $fromName);
        $headers[] = 'Sender: <' . $fromEmail . '>';
        $headers[] = 'Reply-To: ' . $this->formatAddressHeader($replyTo, $replyToName);
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: ' . $messageId;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: VerityScan Transactional Mailer';
        $headers[] = 'X-Auto-Response-Suppress: All';
        $headers[] = 'Auto-Submitted: auto-generated';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body = $this->buildMultipartBody($html, $text, $boundary);
        $sent = @mail($to, $this->encodeHeaderValue($subject), $body, implode("\r\n", $headers), '-f' . $fromEmail);

        if (!$sent) {
            $this->lastError = 'PHP mail() failed to hand off the message.';
        }

        return $sent;
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text, ?string $replyToEmail, string $replyToName): bool {
        $host = (string) ($this->config['smtp_host'] ?? '');
        $port = (int) ($this->config['smtp_port'] ?? 587);
        $user = (string) ($this->config['smtp_user'] ?? '');
        $pass = (string) ($this->config['smtp_pass'] ?? '');
        $encryption = strtolower((string) ($this->config['encryption'] ?? 'tls'));
        $from = $this->effectiveFromEmail();
        $fromName = $this->safeHeaderName((string) ($this->config['from_name'] ?? 'VerityScan'));
        $replyTo = $this->normalizeAddress((string) ($replyToEmail ?: $from));
        $boundary = 'b1_' . md5(uniqid((string) mt_rand(), true));
        $messageId = '<' . md5(uniqid((string) mt_rand(), true)) . '@' . $this->messageHostName() . '>';

        try {
            $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
            $sock = fsockopen($prefix . $host, $port, $errno, $errstr, 15);
            if (!$sock) {
                $this->lastError = 'SMTP connection failed: ' . trim((string) $errstr);
                return false;
            }

            $greeting = $this->smtpRead($sock);
            if (!$this->smtpResponseOk($greeting, ['220'])) {
                $this->lastError = 'SMTP greeting failed.';
                fclose($sock);
                return false;
            }

            $clientName = $this->messageHostName();

            $ehlo = $this->smtpCommand($sock, 'EHLO ' . $clientName);
            if (!$this->smtpResponseOk($ehlo, ['250'])) {
                $this->lastError = 'SMTP EHLO failed.';
                fclose($sock);
                return false;
            }

            if ($encryption === 'tls') {
                $startTls = $this->smtpCommand($sock, 'STARTTLS');
                if (!$this->smtpResponseOk($startTls, ['220'])) {
                    $this->lastError = 'SMTP STARTTLS failed.';
                    fclose($sock);
                    return false;
                }

                if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    $this->lastError = 'SMTP TLS negotiation failed.';
                    fclose($sock);
                    return false;
                }

                $ehlo = $this->smtpCommand($sock, 'EHLO ' . $clientName);
                if (!$this->smtpResponseOk($ehlo, ['250'])) {
                    $this->lastError = 'SMTP EHLO failed after STARTTLS.';
                    fclose($sock);
                    return false;
                }
            }

            if ($user !== '' && $pass !== '') {
                $authLogin = $this->smtpCommand($sock, 'AUTH LOGIN');
                if (!$this->smtpResponseOk($authLogin, ['334'])) {
                    $this->lastError = 'SMTP AUTH LOGIN was rejected.';
                    fclose($sock);
                    return false;
                }

                $authUser = $this->smtpCommand($sock, base64_encode($user));
                if (!$this->smtpResponseOk($authUser, ['334'])) {
                    $this->lastError = 'SMTP username was rejected.';
                    fclose($sock);
                    return false;
                }

                $authPass = $this->smtpCommand($sock, base64_encode($pass));
                if (!$this->smtpResponseOk($authPass, ['235'])) {
                    $this->lastError = 'SMTP password was rejected.';
                    fclose($sock);
                    return false;
                }
            }

            $envelopeFrom = $this->normalizeAddress($user) !== '' ? $this->normalizeAddress($user) : $from;
            $mailFrom = $this->smtpCommand($sock, "MAIL FROM: <{$envelopeFrom}>");
            if (!$this->smtpResponseOk($mailFrom, ['250'])) {
                $this->lastError = 'SMTP MAIL FROM was rejected.';
                fclose($sock);
                return false;
            }

            $rcptTo = $this->smtpCommand($sock, "RCPT TO: <{$to}>");
            if (!$this->smtpResponseOk($rcptTo, ['250', '251'])) {
                $this->lastError = 'SMTP RCPT TO was rejected for that address.';
                fclose($sock);
                return false;
            }

            $dataStart = $this->smtpCommand($sock, 'DATA');
            if (!$this->smtpResponseOk($dataStart, ['354'])) {
                $this->lastError = 'SMTP DATA command was rejected.';
                fclose($sock);
                return false;
            }

            $headers = [];
            $headers[] = 'From: ' . $this->formatAddressHeader($from, $fromName);
            $headers[] = 'Sender: <' . $from . '>';
            $headers[] = 'To: <' . $this->normalizeHeaderText($to) . '>';
            $headers[] = 'Reply-To: ' . $this->formatAddressHeader($replyTo, $replyToName);
            $headers[] = 'Subject: ' . $this->encodeHeaderValue($subject);
            $headers[] = 'Date: ' . date('r');
            $headers[] = 'Message-ID: ' . $messageId;
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'X-Mailer: VerityScan Transactional Mailer';
            $headers[] = 'X-Auto-Response-Suppress: All';
            $headers[] = 'Auto-Submitted: auto-generated';
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

            $body = $this->buildMultipartBody($html, $text, $boundary);
            $message = implode("\r\n", $headers) . "\r\n\r\n" . preg_replace("/(?m)^\./", '..', $body) . "\r\n.\r\n";

            fputs($sock, $message);
            $dataResult = $this->smtpRead($sock);
            if (!$this->smtpResponseOk($dataResult, ['250'])) {
                $this->lastError = 'SMTP server did not accept the message body.';
                fclose($sock);
                return false;
            }

            $this->smtpCommand($sock, 'QUIT');
            fclose($sock);
            return true;
        } catch (\Throwable $e) {
            error_log('MailService SMTP error: ' . $e->getMessage());
            $this->lastError = 'SMTP error: ' . $e->getMessage();
            return false;
        }
    }

    private function buildMultipartBody(string $html, string $text, string $boundary): string {
        $plainText = $text !== '' ? $text : trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES, 'UTF-8'));

        return "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $plainText . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $html . "\r\n\r\n"
            . "--{$boundary}--";
    }

    private function smtpResponseOk(string $response, array $okCodes): bool {
        foreach ($okCodes as $code) {
            if (str_starts_with($response, $code)) {
                return true;
            }
        }

        return false;
    }

    private function smtpCommand($sock, string $command): string {
        fputs($sock, $command . "\r\n");
        return $this->smtpRead($sock);
    }

    private function smtpRead($sock): string {
        $response = '';
        while ($line = fgets($sock, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    public function notifyAdminNewLead(array $lead, string $reportUrl = ''): bool {
        $admin = $this->adminEmail();
        if ($admin === '') {
            $this->lastError = 'Admin notification email is missing.';
            return false;
        }

        $name = $this->escape($lead['contact_name'] ?? 'Unknown');
        $email = $this->escape($lead['email'] ?? '');
        $phone = $this->escape($lead['phone'] ?? '');
        $biz = $this->escape($lead['business_name'] ?? '');
        $url = $this->escape($lead['website_url'] ?? '');
        $safeReportUrl = $this->escape($reportUrl);
        $appName = config('app.name', 'VerityScan');

        $html = <<<HTML
<h2>New Audit Lead</h2>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Phone:</strong> {$phone}</p>
<p><strong>Business:</strong> {$biz}</p>
<p><strong>Website:</strong> {$url}</p>
HTML;
        if ($reportUrl) {
            $html .= "<p><a href=\"{$safeReportUrl}\">View Report</a></p>";
        }

        $text = "New Audit Lead\n"
            . "Name: " . ($lead['contact_name'] ?? 'Unknown') . "\n"
            . "Email: " . ($lead['email'] ?? '') . "\n"
            . "Phone: " . ($lead['phone'] ?? '') . "\n"
            . "Business: " . ($lead['business_name'] ?? '') . "\n"
            . "Website: " . ($lead['website_url'] ?? '') . "\n"
            . ($reportUrl ? "Report: {$reportUrl}\n" : '');

        $replyTo = $this->normalizeAddress((string) ($lead['email'] ?? ''));
        return $this->send(
            $admin,
            "[{$appName}] New Lead: " . ($lead['contact_name'] ?: ($lead['email'] ?? 'Unknown')),
            $html,
            $text,
            $replyTo !== '' ? $replyTo : $this->supportEmail(),
            (string) ($lead['contact_name'] ?? '')
        );
    }

    public function sendReportLink(string $email, string $name, string $reportUrl): bool {
        $appName = config('app.name', 'VerityScan');
        $siteName = $this->siteName();
        $safeName = trim($name) !== '' ? $name : 'there';

        $vars = [
            'site_name' => $siteName,
            'app_name' => $appName,
            'recipient_name' => $safeName,
            'recipient_email' => $email,
            'report_url' => $reportUrl,
            'contact_email' => $this->supportEmail(),
            'contact_name' => $this->supportName(),
            'contact_phone' => $this->supportPhone(),
        ];

        $subjectTemplate = trim((string) ($this->settings['report_email_subject'] ?? 'Your Website Audit Report from {{site_name}}'));
        $htmlTemplate = trim((string) ($this->settings['report_email_html'] ?? ''));
        $textTemplate = trim((string) ($this->settings['report_email_text'] ?? ''));

        if ($htmlTemplate === '') {
            $htmlTemplate = $this->defaultReportEmailHtml();
        }
        if ($textTemplate === '') {
            $textTemplate = $this->defaultReportEmailText();
        }

        $subject = $this->applyTemplate($subjectTemplate, $vars);
        $html = nl2br($this->escape($this->applyTemplate($htmlTemplate, $vars)));
        $html = str_replace(
            ['&lt;a ', '&lt;/a&gt;', '&gt;', '&quot;'],
            ['<a ', '</a>', '>', '"'],
            $html
        );
        $html = $this->replaceTemplateLinks($html);

        $text = $this->applyTemplate($textTemplate, $vars);

        return $this->send($email, $subject, $html, $text, $this->supportEmail(), $this->supportName());
    }

    public function notifyAdminContactRequest(array $contact): bool {
        $admin = $this->adminEmail();
        if ($admin === '') {
            $this->lastError = 'Admin notification email is missing.';
            return false;
        }

        $appName = config('app.name', 'VerityScan');
        $name = $this->escape($contact['name'] ?? '');
        $email = $this->escape($contact['email'] ?? '');
        $phone = $this->escape($contact['phone'] ?? '');
        $company = $this->escape($contact['company'] ?? '');
        $service = $this->escape($contact['service_type'] ?? '');
        $msg = nl2br($this->escape($contact['message'] ?? ''));
        $reportUrl = $this->escape($contact['report_url'] ?? '');

        $html = "<h2>New Contact Request</h2>"
            . "<p><strong>Name:</strong> {$name}</p>"
            . "<p><strong>Email:</strong> {$email}</p>"
            . ($phone !== '' ? "<p><strong>Phone:</strong> {$phone}</p>" : '')
            . ($company !== '' ? "<p><strong>Company:</strong> {$company}</p>" : '')
            . ($service !== '' ? "<p><strong>Service:</strong> {$service}</p>" : '')
            . "<p><strong>Message:</strong><br>{$msg}</p>"
            . ($reportUrl !== '' ? "<p><strong>Related Report:</strong> <a href=\"{$reportUrl}\">View Report</a></p>" : '');

        $text = "New Contact Request\n"
            . "Name: " . ($contact['name'] ?? '') . "\n"
            . "Email: " . ($contact['email'] ?? '') . "\n"
            . (!empty($contact['phone']) ? "Phone: " . $contact['phone'] . "\n" : '')
            . (!empty($contact['company']) ? "Company: " . $contact['company'] . "\n" : '')
            . (!empty($contact['service_type']) ? "Service: " . $contact['service_type'] . "\n" : '')
            . "Message:\n" . ($contact['message'] ?? '') . "\n"
            . (!empty($contact['report_url']) ? "\nReport: " . $contact['report_url'] . "\n" : '');

        $replyTo = $this->normalizeAddress((string) ($contact['email'] ?? ''));
        return $this->send(
            $admin,
            "[{$appName}] New Contact Request from " . ($contact['name'] ?: ($contact['email'] ?? 'Unknown')),
            $html,
            $text,
            $replyTo !== '' ? $replyTo : $this->supportEmail(),
            (string) ($contact['name'] ?? '')
        );
    }

    private function applyTemplate(string $template, array $vars): string {
        $replace = [];
        foreach ($vars as $key => $value) {
            $replace['{{' . $key . '}}'] = (string) $value;
        }
        return strtr($template, $replace);
    }

    private function replaceTemplateLinks(string $html): string {
        return preg_replace_callback(
            '/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/',
            static fn(array $m): string => '<a href="' . htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</a>',
            $html
        ) ?? $html;
    }

    private function defaultReportEmailHtml(): string {
        return <<<HTML
Hi {{recipient_name}},

Your requested website audit from {{site_name}} is ready.

[Open Your Audit Report]({{report_url}})

This email contains the report link you requested. If you want help improving the issues found, reply to this email and we can walk you through the next best steps.

{{contact_name}}
{{contact_email}}
{{contact_phone}}
HTML;
    }

    private function defaultReportEmailText(): string {
        return <<<TEXT
Hi {{recipient_name}},

Your requested website audit from {{site_name}} is ready.

Open your report:
{{report_url}}

This email contains the report link you requested. If you want help improving the issues found, reply to this email and we can walk you through the next best steps.

{{contact_name}}
{{contact_email}}
{{contact_phone}}
TEXT;
    }

    private function effectiveFromEmail(): string {
        $configuredFrom = $this->normalizeAddress((string) ($this->config['from'] ?? ''));
        $smtpUser = $this->normalizeAddress((string) ($this->config['smtp_user'] ?? ''));
        $driver = strtolower((string) ($this->config['driver'] ?? 'mail'));

        if ($driver === 'smtp' && $smtpUser !== '') {
            return $smtpUser;
        }

        return $configuredFrom;
    }

    private function adminEmail(): string {
        return $this->normalizeAddress((string) (($this->config['admin_email'] ?? '') ?: env('ADMIN_EMAIL', '')));
    }

    private function siteName(): string {
        return trim((string) ($this->settings['site_name'] ?? config('app.name', 'VerityScan'))) ?: config('app.name', 'VerityScan');
    }

    private function supportEmail(): string {
        $email = (string) ($this->settings['contact_email'] ?? '');
        if ($this->normalizeAddress($email) !== '') {
            return $email;
        }

        $admin = $this->adminEmail();
        if ($admin !== '') {
            return $admin;
        }

        return (string) ($this->config['from'] ?? '');
    }

    private function supportName(): string {
        return trim((string) ($this->settings['report_email_contact_name'] ?? $this->settings['mail_from_name'] ?? $this->siteName())) ?: $this->siteName();
    }

    private function supportPhone(): string {
        return trim((string) ($this->settings['report_email_contact_phone'] ?? ''));
    }

    private function signatureHtml(): string {
        $lines = array_filter([
            $this->supportName(),
            $this->supportEmail(),
            $this->supportPhone(),
        ], static fn($value): bool => trim((string) $value) !== '');

        if (empty($lines)) {
            return '';
        }

        $escaped = array_map(fn(string $line): string => $this->escape($line), $lines);
        return '<hr><p>' . implode('<br>', $escaped) . '</p>';
    }

    private function signatureText(): string {
        $lines = array_filter([
            $this->supportName(),
            $this->supportEmail(),
            $this->supportPhone(),
        ], static fn($value): bool => trim((string) $value) !== '');

        return empty($lines) ? '' : "\n" . implode("\n", $lines) . "\n";
    }

    private function normalizeAddress(string $email): string {
        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    private function normalizeHeaderText(string $value): string {
        return trim(preg_replace('/[\r\n]+/', ' ', $value));
    }

    private function safeHeaderName(string $value): string {
        return $this->normalizeHeaderText($value);
    }

    private function formatAddressHeader(string $email, string $name = ''): string {
        $email = $this->normalizeHeaderText($email);
        $name = $this->safeHeaderName($name);

        if ($name === '') {
            return $email;
        }

        return $this->encodeHeaderValue($name) . ' <' . $email . '>';
    }

    private function encodeHeaderValue(string $value): string {
        $value = $this->normalizeHeaderText($value);
        return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
    }

    private function messageHostName(): string {
        $host = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $host = preg_replace('/:\d+$/', '', (string) $host);
        $host = preg_replace('/[^A-Za-z0-9\.\-]/', '', (string) $host);
        return $host !== '' ? $host : 'localhost';
    }

    private function escape(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
