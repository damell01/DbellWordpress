<?php
namespace App\Services;

use App\Core\Database;
use App\Models\AuditRequest;
use App\Models\AuditReport;
use App\Models\Lead;
use App\Models\Setting;

class AuditService {
    private AuditEngine      $engine;
    private ScoringEngine    $scorer;
    private MailService      $mailer;
    private Database         $db;
    private ScreenshotService $screenshot;
    private ?bool $supportsPageSpeedColumns = null;

    public function __construct() {
        $this->engine  = new AuditEngine();
        $this->scorer  = new ScoringEngine();
        $this->mailer  = new MailService();
        $this->db      = Database::getInstance();

        // Build ScreenshotService from stored settings (graceful if table missing)
        try {
            $settings          = new Setting();
            $provider          = $settings->get('screenshot_provider', 'mshots');
            $customTpl         = $settings->get('screenshot_api_url', '');
            $verify            = (bool)(int)$settings->get('screenshot_verify', '0');
            $this->screenshot  = new ScreenshotService($provider, $customTpl, $verify);
        } catch (\Throwable $e) {
            $this->screenshot  = new ScreenshotService();
        }
    }

    /**
     * Run a full audit and store the results.
     * Returns the audit report ID on success, or null on failure.
     */
    public function runAndStore(int $auditRequestId, string $url): ?int {
        // Mark as processing
        $this->db->update('audit_requests', ['status' => 'processing'], ['id' => $auditRequestId]);

        try {
            $result  = $this->engine->run($url);
            $scores  = $this->scorer->calculate($result['issues']);
            $summary = $this->scorer->getSummaryText($scores['overall'], $result['issues']);

            // Capture screenshot (non-blocking – failure just means no screenshot)
            $screenshotUrl = null;
            try {
                $screenshotUrl = $this->screenshot->getScreenshotUrl($url);
            } catch (\Throwable $e) {
                error_log('ScreenshotService error for ' . $url . ': ' . $e->getMessage());
            }

            // Insert audit_report
            $token    = bin2hex(random_bytes(16));
            $reportData = [
                'audit_request_id' => $auditRequestId,
                'report_token'     => $token,
                'overall_score'    => $scores['overall'],
                'summary_text'     => $summary,
                'screenshot_url'   => $screenshotUrl,
                'created_at'       => date('Y-m-d H:i:s'),
            ];

            if ($this->auditReportSupportsPageSpeedColumns()) {
                $reportData['pagespeed_mobile_json'] = !empty($result['page_speed']['mobile']) ? json_encode($result['page_speed']['mobile'], JSON_UNESCAPED_SLASHES) : null;
                $reportData['pagespeed_desktop_json'] = !empty($result['page_speed']['desktop']) ? json_encode($result['page_speed']['desktop'], JSON_UNESCAPED_SLASHES) : null;
            }

            $reportId = $this->db->insert('audit_reports', $reportData);

            // Insert scores
            $this->db->insert('audit_scores', [
                'audit_report_id'    => $reportId,
                'seo_score'          => $scores['seo'],
                'accessibility_score'=> $scores['accessibility'],
                'conversion_score'   => $scores['conversion'],
                'technical_score'    => $scores['technical'],
                'local_score'        => $scores['local'],
            ]);

            // Insert issues
            foreach ($result['issues'] as $issue) {
                $this->db->insert('audit_issues', [
                    'audit_report_id' => $reportId,
                    'category'        => $issue['category'],
                    'severity'        => $issue['severity'],
                    'code'            => $issue['code'],
                    'title'           => $issue['title'],
                    'explanation'     => $issue['explanation'],
                    'why_it_matters'  => $issue['why_it_matters'],
                    'how_to_fix'      => $issue['how_to_fix'],
                    'business_impact' => $issue['business_impact'],
                    'detected_value'  => $issue['detected_value'],
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            // Update request
            $this->db->update('audit_requests', [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ], ['id' => $auditRequestId]);

            return $reportId;
        } catch (\Throwable $e) {
            error_log('AuditService::runAndStore error: ' . $e->getMessage());
            try {
                $this->db->update('audit_requests', ['status' => 'failed'], ['id' => $auditRequestId]);
            } catch (\Throwable $updateError) {
                error_log('AuditService::runAndStore failed-status update error: ' . $updateError->getMessage());
            }
            return null;
        }
    }

    public function createAuditRequest(string $url, ?int $leadId, string $ip, string $userAgent): int {
        return $this->db->insert('audit_requests', [
            'lead_id'        => $leadId,
            'website_url'    => $url,
            'normalized_url' => $url,
            'status'         => 'pending',
            'ip_address'     => $ip,
            'user_agent'     => $userAgent,
            'requested_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function createLead(array $data): int {
        return $this->db->insert('leads', [
            'website_url'   => $data['website_url'] ?? '',
            'business_name' => $data['business_name'] ?? '',
            'contact_name'  => $data['contact_name'] ?? '',
            'email'         => $data['email'] ?? '',
            'phone'         => $data['phone'] ?? '',
            'notes'         => $data['notes'] ?? '',
            'source'        => $data['source'] ?? 'audit',
            'status'        => 'new',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    private function auditReportSupportsPageSpeedColumns(): bool {
        if ($this->supportsPageSpeedColumns !== null) {
            return $this->supportsPageSpeedColumns;
        }

        try {
            $count = (int) $this->db->scalar(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE()
                   AND table_name = 'audit_reports'
                   AND column_name IN ('pagespeed_mobile_json', 'pagespeed_desktop_json')"
            );
            $this->supportsPageSpeedColumns = $count >= 2;
        } catch (\Throwable $e) {
            $this->supportsPageSpeedColumns = false;
        }

        return $this->supportsPageSpeedColumns;
    }
}
