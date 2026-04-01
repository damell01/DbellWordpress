<?php
namespace App\Controllers;

use App\Core\{Request, Session, Database};
use App\Models\{AuditReport};
use App\Services\{AuditService, UrlNormalizer, ScoringEngine};

class AuditController extends BaseController {
    private AuditService $auditService;
    private UrlNormalizer $urlNorm;
    private ScoringEngine $scorer;

    public function __construct() {
        parent::__construct();
        $this->auditService = new AuditService();
        $this->urlNorm = new UrlNormalizer();
        $this->scorer = new ScoringEngine();
    }

    public function form(Request $request): void {
        $this->view('audit', ['title' => 'Free Website Audit']);
    }

    public function submit(Request $request): void {
        if (!$request->isPost()) {
            $this->redirect('audit');
            return;
        }

        $rawUrl = trim($request->post('website_url', ''));
        if ($rawUrl === '') {
            Session::setFlash('error', 'Please enter a website URL.');
            $this->redirect('audit');
            return;
        }

        $db = Database::getInstance();
        $ip = $request->ip();
        $limit = (int) $this->settings->get('rate_limit_audits', config('app.rate_limit.audits', 5));
        $window = (int) $this->settings->get('rate_limit_window', config('app.rate_limit.window', 3600));
        $window = max(60, $window);
        $recentCount = (int) $db->scalar(
            "SELECT COUNT(*) FROM audit_requests WHERE ip_address = ? AND requested_at >= DATE_SUB(NOW(), INTERVAL {$window} SECOND)",
            [$ip]
        );

        if ($recentCount >= $limit) {
            Session::setFlash('error', 'You have reached the audit limit. Please try again later.');
            $this->redirect('audit');
            return;
        }

        try {
            $url = $this->urlNorm->normalize($rawUrl);
        } catch (\InvalidArgumentException $e) {
            Session::setFlash('error', 'Please enter a valid website URL (e.g., https://example.com).');
            $this->redirect('audit');
            return;
        }

        $email = trim($request->post('email', ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Please enter a valid email address.');
            Session::setFlash('_old', $request->all());
            $this->redirect('audit');
            return;
        }

        $name = trim($request->post('contact_name', ''));
        $leadId = $this->auditService->createLead([
            'website_url'   => $url,
            'contact_name'  => $name,
            'email'         => $email,
            'phone'         => $request->post('phone', ''),
            'business_name' => $request->post('business_name', ''),
            'notes'         => $request->post('notes', ''),
            'source'        => 'audit_form',
        ]);

        $auditRequestId = $this->auditService->createAuditRequest($url, $leadId, $ip, $request->userAgent());
        $reportId = $this->auditService->runAndStore($auditRequestId, $url);

        if (!$reportId) {
            Session::setFlash('error', 'The audit could not be completed. The website may be unreachable.');
            $this->redirect('audit');
            return;
        }

        $reportModel = new AuditReport();
        $report = $reportModel->find($reportId);
        if (!$report) {
            $this->redirect('audit');
            return;
        }

        $reportUrl = url("report/{$report['report_token']}");
        $mailer = new \App\Services\MailService();

        $lead = $leadId ? $db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]) : [];
        $adminNotified = false;
        if ($lead) {
            $adminNotified = $mailer->notifyAdminNewLead($lead, $reportUrl);
        }

        $reportEmailSent = false;
        if ($email !== '' && !empty($report['report_token'])) {
            $reportEmailSent = $mailer->sendReportLink($email, $name, $reportUrl);
        }

        if ($email !== '') {
            if ($reportEmailSent) {
                Session::setFlash('success', 'Audit complete. Your report email was sent to ' . $email . '.');
            } else {
                $detail = $mailer->getLastError();
                Session::setFlash(
                    'error',
                    'Audit complete, but report email delivery failed.' . ($detail !== '' ? ' ' . $detail : '')
                );
            }
        } elseif (!$adminNotified && $lead) {
            $detail = $mailer->getLastError();
            Session::setFlash(
                'error',
                'Audit complete, but admin notification email delivery failed.' . ($detail !== '' ? ' ' . $detail : '')
            );
        }

        $this->redirect("report/{$report['report_token']}");
    }

    public function requestHelp(Request $request, array $params): void {
        $token = $params['token'] ?? '';
        $model = new AuditReport();
        $report = $model->findByToken($token);

        if (!$report) {
            abort(404, 'Report not found.');
        }

        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));
        $phone = trim($request->post('phone', ''));
        $company = trim($request->post('company', ''));
        $message = trim($request->post('message', ''));
        $service = trim($request->post('service_type', ''));

        if ($name === '' || $email === '') {
            Session::setFlash('error', 'Please enter your name and email.');
            $this->redirect("report/{$token}#request-help");
            return;
        }

        $db = Database::getInstance();
        $full = $model->findFullReport($report['id']);
        $leadId = (!empty($full) && !empty($full['request'])) ? ($full['request']['lead_id'] ?? null) : null;
        $websiteUrl = (!empty($full) && !empty($full['request'])) ? ($full['request']['website_url'] ?? '') : '';

        if (!$leadId && $email !== '') {
            $existingLead = $db->fetch("SELECT id FROM leads WHERE email = ? LIMIT 1", [$email]);
            if ($existingLead) {
                $leadId = $existingLead['id'];
            } else {
                $leadId = $db->insert('leads', [
                    'website_url'   => $websiteUrl,
                    'contact_name'  => $name,
                    'email'         => $email,
                    'phone'         => $phone,
                    'business_name' => $company,
                    'source'        => 'report_help',
                    'status'        => 'new',
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($leadId) {
            $existingLead = $db->fetch("SELECT * FROM leads WHERE id = ? LIMIT 1", [$leadId]);
            if ($existingLead) {
                $db->update('leads', [
                    'website_url'   => $websiteUrl !== '' ? $websiteUrl : ($existingLead['website_url'] ?? ''),
                    'contact_name'  => $name !== '' ? $name : ($existingLead['contact_name'] ?? ''),
                    'email'         => $email !== '' ? $email : ($existingLead['email'] ?? ''),
                    'phone'         => $phone !== '' ? $phone : ($existingLead['phone'] ?? ''),
                    'business_name' => $company !== '' ? $company : ($existingLead['business_name'] ?? ''),
                    'notes'         => !empty($existingLead['notes']) ? $existingLead['notes'] : $message,
                ], ['id' => $leadId]);
            }
        }

        $db->insert('contact_requests', [
            'lead_id'         => $leadId,
            'audit_report_id' => $report['id'],
            'name'            => $name,
            'email'           => $email,
            'phone'           => $phone,
            'company'         => $company,
            'message'         => $message,
            'service_type'    => $service,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $mailer = new \App\Services\MailService();
        $adminSent = $mailer->notifyAdminContactRequest([
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'company'      => $company,
            'message'      => $message,
            'service_type' => $service,
            'report_url'   => url("report/{$token}"),
        ]);

        if ($adminSent) {
            Session::setFlash('success', 'Thank you! Your request was sent and our team was notified by email.');
        } else {
            $detail = $mailer->getLastError();
            Session::setFlash(
                'error',
                'Your request was saved, but notification email delivery failed.' . ($detail !== '' ? ' ' . $detail : '')
            );
        }

        $this->redirect("report/{$token}#request-help");
    }

    public function report(Request $request, array $params): void {
        $token = $params['token'] ?? '';
        $model = new AuditReport();
        $report = $model->findByToken($token);

        if (!$report) {
            abort(404, 'Report not found.');
        }

        $full = $model->findFullReport($report['id']);
        $model->incrementViews($report['id']);

        $scores = $full['scores'] ?? [];
        $issues = $full['issues'] ?? [];
        $requestData = $full['request'] ?? [];
        $previousReport = !empty($report['audit_request_id'])
            ? $model->findPreviousForRequest((int) $report['audit_request_id'], (int) $report['id'])
            : null;
        $feedbackSummary = $model->feedbackSummary((int) $report['id']);

        $comparison = null;
        if (!empty($previousReport)) {
            $comparison = [
                'report' => $previousReport,
                'score_delta' => (int) ($report['overall_score'] ?? 0) - (int) ($previousReport['overall_score'] ?? 0),
                'created_at' => $previousReport['created_at'] ?? null,
            ];
        }

        $issuesBySeverity = [];
        $issuesByCategory = [];
        foreach ($issues as $issue) {
            $issuesBySeverity[$issue['severity']][] = $issue;
            $issuesByCategory[$issue['category']][] = $issue;
        }

        $grade = $this->scorer->getGrade($report['overall_score'] ?? 0);
        $pageSpeedData = [
            'mobile' => $full['pagespeed_mobile'] ?? null,
            'desktop' => $full['pagespeed_desktop'] ?? null,
        ];

        $this->view('report', [
            'title'            => 'Audit Report - ' . ($requestData['website_url'] ?? ''),
            'report'           => $report,
            'full'             => $full,
            'scores'           => $scores,
            'issues'           => $issues,
            'issuesBySeverity' => $issuesBySeverity,
            'issuesByCategory' => $issuesByCategory,
            'grade'            => $grade,
            'requestData'      => $requestData,
            'lead'             => $full['lead'] ?? null,
            'comparison'       => $comparison,
            'feedbackSummary'  => $feedbackSummary,
            'pageSpeedData'    => $pageSpeedData,
        ]);
    }

    public function submitFeedback(Request $request, array $params): void {
        $token = $params['token'] ?? '';
        $model = new AuditReport();
        $report = $model->findByToken($token);

        if (!$report) {
            abort(404, 'Report not found.');
        }

        $issueId = (int) $request->post('issue_id', 0);
        $feedbackType = trim($request->post('feedback_type', ''));
        $notes = trim($request->post('notes', ''));
        $allowed = ['incorrect', 'helpful'];

        if ($issueId < 1 || !in_array($feedbackType, $allowed, true)) {
            Session::setFlash('error', 'Please choose valid issue feedback.');
            $this->redirect("report/{$token}#issues-section");
            return;
        }

        $db = Database::getInstance();
        $issue = $db->fetch(
            "SELECT id FROM audit_issues WHERE id = ? AND audit_report_id = ?",
            [$issueId, $report['id']]
        );

        if (!$issue) {
            Session::setFlash('error', 'That issue could not be matched to this report.');
            $this->redirect("report/{$token}#issues-section");
            return;
        }

        try {
            $db->insert('audit_issue_feedback', [
                'audit_report_id' => $report['id'],
                'audit_issue_id'  => $issueId,
                'feedback_type'   => $feedbackType,
                'notes'           => $notes,
                'ip_address'      => $request->ip(),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
            Session::setFlash('success', 'Thanks. Your feedback helps improve future scans.');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Feedback storage is not ready yet. Run the latest schema update first.');
        }

        $this->redirect("report/{$token}#issue-{$issueId}");
    }
}
