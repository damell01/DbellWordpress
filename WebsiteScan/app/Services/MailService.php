<?php
namespace App\Services;

use App\Models\Setting;

class MailService {
    private array $config;

    public function __construct() {
        $this->config = require base_path('config/mail.php');

        try {
            $settings = new Setting();
            $overrides = [
                'driver' => $settings->get('mail_driver', ''),
                'from' => $settings->get('mail_from', ''),
                'from_name' => $settings->get('mail_from_name', ''),
                'smtp_host' => $settings->get('smtp_host', ''),
                'smtp_port' => $settings->get('smtp_port', ''),
                'smtp_user' => $settings->get('smtp_user', ''),
                'smtp_pass' => $settings->get('smtp_pass', ''),
                'encryption' => $settings->get('smtp_encryption', ''),
                'admin_email' => $settings->get('admin_email', ''),
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
            // Fall back to env/file config when settings storage is unavailable.
        }
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool {
        if (empty($this->config['from'])) return false;

        $driver = $this->config['driver'] ?? 'mail';

        if ($driver === 'smtp' && !empty($this->config['smtp_host'])) {
            return $this->sendSmtp($to, $subject, $htmlBody, $textBody);
        }

        return $this->sendNative($to, $subject, $htmlBody, $textBody);
    }

    private function sendNative(string $to, string $subject, string $html, string $text): bool {
        $fromName  = $this->config['from_name'] ?? 'VerityScan';
        $fromEmail = $this->config['from'];
        $boundary  = md5(uniqid());

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: VerityScan\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $body .= ($text ?: strip_tags($html)) . "\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $body .= $html . "\r\n\r\n";
        $body .= "--{$boundary}--";

        return @mail($to, $subject, $body, $headers);
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text): bool {
        $host       = $this->config['smtp_host'];
        $port       = $this->config['smtp_port'];
        $user       = $this->config['smtp_user'];
        $pass       = $this->config['smtp_pass'];
        $encryption = $this->config['encryption'] ?? 'tls';
        $from       = $this->config['from'];
        $fromName   = $this->config['from_name'] ?? 'VerityScan';
        $boundary   = md5(uniqid());

        try {
            $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
            $sock   = fsockopen($prefix . $host, $port, $errno, $errstr, 15);
            if (!$sock) return false;

            $this->smtpRead($sock);

            if ($encryption === 'tls') {
                $this->smtpCommand($sock, "EHLO " . gethostname());
                $this->smtpCommand($sock, "STARTTLS");
                stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }

            $this->smtpCommand($sock, "EHLO " . gethostname());
            $this->smtpCommand($sock, "AUTH LOGIN");
            $this->smtpCommand($sock, base64_encode($user));
            $this->smtpCommand($sock, base64_encode($pass));
            $this->smtpCommand($sock, "MAIL FROM: <{$from}>");
            $this->smtpCommand($sock, "RCPT TO: <{$to}>");
            $this->smtpCommand($sock, "DATA");

            $body  = "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
            $body .= ($text ?: strip_tags($html)) . "\r\n\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $html . "\r\n\r\n";
            $body .= "--{$boundary}--";

            $message  = "From: {$fromName} <{$from}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: {$subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $message .= "\r\n" . $body . "\r\n.\r\n";

            fputs($sock, $message);
            $this->smtpRead($sock);
            $this->smtpCommand($sock, "QUIT");
            fclose($sock);
            return true;
        } catch (\Throwable $e) {
            error_log('MailService SMTP error: ' . $e->getMessage());
            return false;
        }
    }

    private function smtpCommand($sock, string $command): string {
        fputs($sock, $command . "\r\n");
        return $this->smtpRead($sock);
    }

    private function smtpRead($sock): string {
        $response = '';
        while ($line = fgets($sock, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    public function notifyAdminNewLead(array $lead, string $reportUrl = ''): bool {
        $admin  = $this->config['admin_email'] ?? env('ADMIN_EMAIL', '');
        if (empty($admin)) return false;

        $name    = htmlspecialchars($lead['contact_name'] ?? 'Unknown');
        $email   = htmlspecialchars($lead['email'] ?? '');
        $phone   = htmlspecialchars($lead['phone'] ?? '');
        $biz     = htmlspecialchars($lead['business_name'] ?? '');
        $url     = htmlspecialchars($lead['website_url'] ?? '');
        $appName = config('app.name', 'VerityScan');

        $html = <<<HTML
<h2>New Audit Lead</h2>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Phone:</strong> {$phone}</p>
<p><strong>Business:</strong> {$biz}</p>
<p><strong>Website:</strong> {$url}</p>
HTML;
        if ($reportUrl) $html .= "<p><a href=\"{$reportUrl}\">View Report</a></p>";

        return $this->send($admin, "[{$appName}] New Lead: {$name}", $html);
    }

    public function sendReportLink(string $email, string $name, string $reportUrl): bool {
        $appName = config('app.name', 'VerityScan');
        $safeName = htmlspecialchars($name);
        $safeUrl  = htmlspecialchars($reportUrl);
        $html = <<<HTML
<h2>Your {$appName} Audit Report Is Ready!</h2>
<p>Hi {$safeName},</p>
<p>Your free website audit report is ready. Click below to view your results:</p>
<p><a href="{$safeUrl}" style="background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;">View My Report</a></p>
<p>If you have questions or would like help fixing the issues, please reply to this email or contact us.</p>
HTML;
        return $this->send($email, "Your Website Audit Report – {$appName}", $html);
    }

    public function notifyAdminContactRequest(array $contact): bool {
        $admin = $this->config['admin_email'] ?? env('ADMIN_EMAIL', '');
        if (empty($admin)) return false;
        $appName = config('app.name', 'VerityScan');
        $name    = htmlspecialchars($contact['name'] ?? '');
        $email   = htmlspecialchars($contact['email'] ?? '');
        $service = htmlspecialchars($contact['service_type'] ?? '');
        $msg     = htmlspecialchars($contact['message'] ?? '');
        $html    = "<h2>New Contact Request</h2><p><strong>Name:</strong> {$name}</p><p><strong>Email:</strong> {$email}</p><p><strong>Service:</strong> {$service}</p><p><strong>Message:</strong> {$msg}</p>";
        return $this->send($admin, "[{$appName}] New Contact Request from {$name}", $html);
    }
}
