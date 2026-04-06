<?php
namespace App\Controllers;

use App\Core\{Request, Session, Database};
use App\Models\{AuditReport, AuditRequest, Lead, ContactRequest, Setting};
use App\Services\{CsvExporter, UpgradeService};

class AdminController extends BaseController {
    private Database $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function dashboard(Request $request): void {
        $reportModel  = new AuditReport();
        $leadModel    = new Lead();
        $contactModel = new ContactRequest();

        $stats = [
            'total_audits'    => $this->db->count('audit_requests'),
            'total_leads'     => $this->db->count('leads'),
            'total_contacts'  => $this->db->count('contact_requests'),
            'audits_this_week'=> (int)$this->db->scalar("SELECT COUNT(*) FROM audit_requests WHERE requested_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'avg_score_30d'   => (int)$this->db->scalar("SELECT COALESCE(AVG(rep.overall_score), 0) FROM audit_reports rep WHERE rep.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'help_requests_30d' => (int)$this->db->scalar("SELECT COUNT(*) FROM contact_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
        ];

        $this->adminView('dashboard', [
            'title'           => 'Dashboard',
            'stats'           => $stats,
            'recentLeads'     => $leadModel->recent(8),
            'recentScans'     => $reportModel->recentWithRequest(8),
            'recentContacts'  => $contactModel->recent(5),
            'commonIssues'    => $reportModel->commonIssues(8),
            'scansByDay'      => $this->db->fetchAll("SELECT DATE(requested_at) as day, COUNT(*) as total FROM audit_requests WHERE requested_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(requested_at) ORDER BY day ASC"),
            'leadIntelligence'=> $this->db->fetchAll(
                "SELECT l.id, l.contact_name, l.email, l.website_url, l.status, l.source, l.created_at,
                        COUNT(DISTINCT ar.id) AS scan_count,
                        COUNT(DISTINCT cr.id) AS help_requests,
                        MAX(ar.requested_at) AS last_scan_at,
                        ROUND(AVG(rep.overall_score)) AS avg_score
                 FROM leads l
                 LEFT JOIN audit_requests ar ON ar.lead_id = l.id
                 LEFT JOIN audit_reports rep ON rep.audit_request_id = ar.id
                 LEFT JOIN contact_requests cr ON cr.lead_id = l.id
                 GROUP BY l.id, l.contact_name, l.email, l.website_url, l.status, l.source, l.created_at
                 ORDER BY help_requests DESC, scan_count DESC, l.created_at DESC
                 LIMIT 8"
            ),
        ]);
    }

    public function leads(Request $request): void {
        $leadModel = new Lead();
        $page      = max(1, (int)$request->get('page', 1));
        $search    = $request->get('search', '');
        $status    = $request->get('status', '');

        if ($search) {
            $data = $leadModel->search($search, $page);
        } elseif ($status) {
            $data = $leadModel->paginate($page, 20, "status = ?", [$status]);
        } else {
            $data = $leadModel->paginate($page, 20);
        }

        $this->adminView('leads', [
            'title'     => 'Leads',
            'leadsData' => $data,
            'search'    => $search,
            'status'    => $status,
        ]);
    }

    public function viewLead(Request $request, array $params): void {
        $id   = (int)($params['id'] ?? 0);
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        if (!$lead) abort(404);

        // Ensure follow-up fields exist with safe defaults when columns are absent
        $lead += [
            'follow_up_stage'   => 0,
            'service_interest'  => null,
            'source_page'       => null,
            'last_contacted_at' => null,
            'next_follow_up_at' => null,
        ];

        try {
            $audits = $this->db->fetchAll(
                "SELECT ar.*, rep.report_token, rep.overall_score FROM audit_requests ar LEFT JOIN audit_reports rep ON rep.audit_request_id = ar.id WHERE ar.lead_id = ? ORDER BY ar.requested_at DESC",
                [$id]
            );
        } catch (\Throwable $e) {
            $audits = [];
        }

        try {
            $notes = $this->db->fetchAll(
                "SELECT ln.*, u.name AS admin_name FROM lead_notes ln LEFT JOIN users u ON u.id = ln.user_id WHERE ln.lead_id = ? ORDER BY ln.created_at DESC",
                [$id]
            );
        } catch (\Throwable $e) {
            $notes = [];
        }

        try {
            $emailLog = $this->db->fetchAll(
                "SELECT * FROM email_log WHERE lead_id = ? ORDER BY sent_at DESC LIMIT 20",
                [$id]
            );
        } catch (\Throwable $e) {
            $emailLog = [];
        }

        // Build the next pipeline message for preview
        $nextStage   = (int)($lead['follow_up_stage'] ?? 0) + 1;
        $nextMessage = $nextStage <= 4 ? $this->buildFollowUpMessage($nextStage, $lead) : null;

        $this->adminView('lead-detail', [
            'title'       => 'Lead: ' . ($lead['contact_name'] ?: $lead['email']),
            'lead'        => $lead,
            'audits'      => $audits,
            'notes'       => $notes,
            'emailLog'    => $emailLog,
            'nextStage'   => $nextStage,
            'nextMessage' => $nextMessage,
        ]);
    }

    public function updateLead(Request $request, array $params): void {
        $id      = (int)($params['id'] ?? 0);
        $allowed = ['new','reviewed','contacted','quote_sent','closed_won','closed_lost'];
        $status  = in_array($request->post('status'), $allowed) ? $request->post('status') : 'new';
        $this->db->update('leads', ['status' => $status], ['id' => $id]);
        Session::setFlash('success', 'Lead status updated.');
        $this->redirect("admin/leads/{$id}");
    }

    public function addLeadNote(Request $request, array $params): void {
        $id   = (int)($params['id'] ?? 0);
        $note = trim($request->post('note', ''));
        if ($id && $note !== '') {
            $userId = Session::get('user_id');
            try {
                $this->db->insert('lead_notes', [
                    'lead_id'    => $id,
                    'user_id'    => $userId ?: null,
                    'note'       => $note,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                Session::setFlash('success', 'Note added.');
            } catch (\Throwable $e) {
                Session::setFlash('error', 'Could not save note. Run the schema upgrade and try again.');
            }
        }
        $this->redirect("admin/leads/{$id}");
    }

    public function sendLeadMessage(Request $request, array $params): void {
        $id   = (int)($params['id'] ?? 0);
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$id]);
        if (!$lead) abort(404);

        $subject = trim((string) $request->post('subject', ''));
        $body    = trim((string) $request->post('body', ''));

        if ($subject === '' || $body === '') {
            Session::setFlash('error', 'Subject and message body are required.');
            $this->redirect("admin/leads/{$id}");
            return;
        }

        $email = $lead['email'] ?? '';
        if ($email === '') {
            Session::setFlash('error', 'This lead has no email address on file.');
            $this->redirect("admin/leads/{$id}");
            return;
        }

        $stage = (int)($lead['follow_up_stage'] ?? 0) + 1;

        $mailer  = new \App\Services\MailService();
        $htmlBody = nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $sent    = $mailer->send($email, $subject, $htmlBody, $body);

        $logStatus = $sent ? 'sent' : 'failed';

        try {
            $this->db->insert('email_log', [
                'lead_id'         => $id,
                'email_stage'     => $stage,
                'recipient_email' => $email,
                'subject'         => $subject,
                'body'            => $body,
                'status'          => $logStatus,
                'sent_at'         => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // email_log table may not exist yet; ignore log failure
        }

        if ($sent) {
            // Advance the follow-up stage and update timestamps
            $daysMap  = [1 => 1, 2 => 3, 3 => 5, 4 => null];
            $days     = $daysMap[$stage] ?? null;
            $nextDate = $days !== null ? date('Y-m-d H:i:s', strtotime("+{$days} days")) : null;

            $updateData = [
                'follow_up_stage'   => $stage,
                'last_contacted_at' => date('Y-m-d H:i:s'),
                'next_follow_up_at' => $nextDate,
            ];
            // Move status from 'new' → 'contacted' on first send
            if (($lead['status'] ?? 'new') === 'new') {
                $updateData['status'] = 'contacted';
            }
            try {
                $this->db->update('leads', $updateData, ['id' => $id]);
            } catch (\Throwable $e) {
                // column may not exist on old schema; non-fatal
            }

            Session::setFlash('success', "Message sent to {$email} (Stage {$stage}).");
        } else {
            $err = $mailer->getLastError();
            Session::setFlash('error', 'Failed to send message.' . ($err !== '' ? ' ' . $err : ' Check your mail settings.'));
        }

        $this->redirect("admin/leads/{$id}");
    }

    /**
     * Build the follow-up email subject + body for a given stage, mirroring cron/follow-up.php.
     */
    private function buildFollowUpMessage(int $stage, array $lead): array {
        $name       = $lead['contact_name'] ?: 'there';
        $firstName  = explode(' ', trim($name))[0];
        $bizSuffix  = !empty($lead['business_name']) ? " ({$lead['business_name']})" : '';

        switch ($stage) {
            case 1:
                $subject = "Thanks for reaching out, {$firstName}! Here's what's next 🙌";
                $body  = "Hey {$firstName},\n\n";
                $body .= "Thanks for reaching out to DBell Creations{$bizSuffix}! I wanted to personally follow up and make sure you got everything you need.\n\n";
                $body .= "We help small businesses like yours with:\n";
                $body .= "✅ Affordable websites (starting at just \$350)\n";
                $body .= "✅ Custom software & business automation\n";
                $body .= "✅ SEO that actually gets you more traffic\n\n";
                $body .= "One quick thing — have you run a free website audit yet? It's completely free and will show you exactly what's hurting your site's performance and rankings:\n";
                $body .= "👉 https://www.dbellcreations.com/scan.html\n\n";
                $body .= "I'll be back in touch shortly. In the meantime, feel free to reply to this email with any questions!\n\n";
                $body .= "Talk soon,\nDBell Creations\n📞 251-406-2292\n🌐 https://www.dbellcreations.com";
                break;

            case 2:
                $subject = "Quick question for you, {$firstName} — is your website holding you back?";
                $body  = "Hey {$firstName},\n\n";
                $body .= "I wanted to share something that might be helpful.\n\n";
                $body .= "Most small business websites we audit have at least 3-5 issues that are quietly killing their results — things like:\n\n";
                $body .= "❌ Slow load times (Google penalizes sites that take more than 3 seconds to load)\n";
                $body .= "❌ No clear call-to-action (visitors don't know what to do next)\n";
                $body .= "❌ Poor mobile experience (60%+ of traffic is on phones)\n";
                $body .= "❌ Missing SEO basics (your site isn't being found for the right keywords)\n";
                $body .= "❌ No lead capture (you're losing potential customers daily)\n\n";
                $body .= "Any of those sound familiar?\n\n";
                $body .= "If so, our free website audit will catch all of these and give you a prioritized action plan:\n";
                $body .= "👉 Run your free audit: https://www.dbellcreations.com/scan.html\n\n";
                $body .= "Just reply to this email if you have questions — happy to help!\n\n";
                $body .= "— DBell Creations\n📞 251-406-2292";
                break;

            case 3:
                $subject = "Still thinking it over? Here's our pricing 👇";
                $body  = "Hey {$firstName},\n\n";
                $body .= "Just wanted to circle back quickly — wanted to make sure you saw our website packages.\n\n";
                $body .= "Our most popular options:\n\n";
                $body .= "⭐ Starter Website — \$350 (SALE)\n";
                $body .= "   Perfect for getting a professional web presence fast.\n\n";
                $body .= "⭐ Business Website — \$750 (SALE)\n";
                $body .= "   Full site you can manage yourself, with lead forms and SEO built in.\n\n";
                $body .= "⭐ Custom Build — \$1,000–\$1,500+\n";
                $body .= "   For businesses that need advanced features or custom designs.\n\n";
                $body .= "👉 See all pricing: https://www.dbellcreations.com/pricing.html\n\n";
                $body .= "If you're not sure which option is right for you, just reply here and I'll help you figure it out — no pressure, no obligation.\n\n";
                $body .= "— DBell Creations\n📞 251-406-2292\n🌐 https://www.dbellcreations.com";
                break;

            case 4:
                $subject = "Last check-in from DBell Creations 👋";
                $body  = "Hey {$firstName},\n\n";
                $body .= "I know you're busy — just wanted to do one final check-in.\n\n";
                $body .= "If you're still looking to improve your online presence — whether it's a new website, better SEO, or a custom software solution — I'd love to help.\n\n";
                $body .= "Even if the timing isn't right now, here are some resources to save for later:\n";
                $body .= "📋 View our pricing: https://www.dbellcreations.com/pricing.html\n";
                $body .= "🔍 Free website audit: https://www.dbellcreations.com/scan.html\n";
                $body .= "📞 Call us: 251-406-2292\n\n";
                $body .= "No need to reply if now isn't the right time — we'll be here when you're ready.\n\n";
                $body .= "Wishing you the best,\nDBell Creations\n🌐 https://www.dbellcreations.com";
                break;

            default:
                $subject = "Following up from DBell Creations";
                $body    = "Hey {$firstName},\n\nJust following up from DBell Creations. Reply anytime if we can help!\n\n— DBell Creations\n📞 251-406-2292";
        }

        return ['subject' => $subject, 'body' => $body];
    }

    public function scans(Request $request): void {
        $page   = max(1, (int)$request->get('page', 1));
        $search = $request->get('search', '');
        $where  = '';
        $params = [];
        if ($search) { $where = "req.website_url LIKE ?"; $params = ['%'.$search.'%']; }

        $total = (int)$this->db->scalar(
            "SELECT COUNT(*) FROM audit_requests req" . ($where ? " WHERE {$where}" : ''),
            $params
        );
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        $items   = $this->db->fetchAll(
            "SELECT req.*, rep.report_token, rep.overall_score FROM audit_requests req LEFT JOIN audit_reports rep ON rep.audit_request_id = req.id " . ($where ? "WHERE {$where} " : '') . "ORDER BY req.requested_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $this->adminView('scans', [
            'title'  => 'Scans',
            'items'  => $items,
            'total'  => $total,
            'page'   => $page,
            'perPage'=> $perPage,
            'search' => $search,
        ]);
    }

    public function contacts(Request $request): void {
        $page   = max(1, (int)$request->get('page', 1));
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $model  = new ContactRequest();

        if ($search) {
            $data = $model->search($search, $page, 20, $status);
        } elseif ($status) {
            $data = $model->filterByStatus($status, $page);
        } else {
            $data = $model->paginate($page, 20, '', [], 'created_at DESC');
        }

        $statusCounts = $model->countByStatus();
        $this->adminView('contacts', [
            'title'         => 'Contact Requests',
            'contactsData'  => $data,
            'search'        => $search,
            'status'        => $status,
            'statusCounts'  => $statusCounts,
        ]);
    }

    public function viewContact(Request $request, array $params): void {
        $id = (int)($params['id'] ?? 0);
        $model = new ContactRequest();
        $contact = $model->withLead($id);
        if (!$contact) abort(404);

        // Mark as read automatically when admin views it
        if (($contact['status'] ?? 'new') === 'new') {
            $model->updateStatus($id, 'read');
            $contact['status'] = 'read';
        }

        $this->adminView('contact-detail', [
            'title'   => 'Contact: ' . ($contact['name'] ?? 'Details'),
            'contact' => $contact,
        ]);
    }

    public function updateContactStatus(Request $request, array $params): void {
        $id     = (int)($params['id'] ?? 0);
        $model  = new ContactRequest();
        $allowed = ['new', 'read', 'replied', 'archived'];
        $status  = in_array($request->post('status'), $allowed) ? $request->post('status') : 'read';
        $model->updateStatus($id, $status);

        $note = trim((string) $request->post('notes', ''));
        if ($note !== '') {
            $model->addNote($id, $note);
        }

        Session::setFlash('success', 'Contact updated.');
        $this->redirect("admin/contacts/{$id}");
    }

    public function exportContacts(Request $request): void {
        $model    = new ContactRequest();
        $contacts = $model->exportAll([
            'status' => $request->get('status', ''),
            'from'   => $request->get('from', ''),
            'to'     => $request->get('to', ''),
        ]);
        $exporter = new CsvExporter();
        $csv      = $exporter->exportContacts($contacts);
        $exporter->sendDownload($csv, 'contacts-' . date('Y-m-d') . '.csv');
    }

    public function settings(Request $request): void {
        if ($request->isPost()) {
            $keys = ['site_name', 'hero_headline', 'hero_subheadline', 'contact_email',
                     'cta_text', 'cta_subtext', 'rate_limit_audits', 'rate_limit_window',
                     'require_email_for_report',
                     'screenshot_provider', 'screenshot_api_url', 'screenshot_verify',
                     'mail_driver', 'mail_from', 'mail_from_name', 'smtp_host', 'smtp_port',
                     'smtp_user', 'smtp_pass', 'smtp_encryption', 'admin_email',
                     'report_email_contact_name', 'report_email_contact_phone',
                     'report_email_subject', 'report_email_html', 'report_email_text',
                     'google_maps_api_key', 'google_pagespeed_api_key',
                     'enable_google_places_lookup', 'enable_pagespeed_lookup'];
            $saveData = [];
            foreach ($keys as $key) {
                $saveData[$key] = $request->post($key, '');
            }
            $this->settings->setMany($saveData);
            $debugSaved = $this->writeDebugOverride(!empty($request->post('debug_mode', '')));
            Session::setFlash(
                $debugSaved ? 'success' : 'error',
                $debugSaved
                    ? 'Settings saved.'
                    : 'Settings saved, but debug mode could not be updated. Make sure the config folder is writable.'
            );
            $this->redirect('admin/settings');
            return;
        }
        $debugFile = base_path('config/debug.local.php');
        $debugLocal = file_exists($debugFile) ? (require $debugFile) : [];
        $all = $this->settings->getAll();
        $all['debug_mode'] = !empty($debugLocal['debug']) ? '1' : '';
        $this->adminView('settings', ['title' => 'Settings', 'all' => $all]);
    }

    public function schemaUpgrade(Request $request): void {
        try {
            $upgrade = new UpgradeService($this->db);
            $result = $upgrade->run(false);
            $details = array_map(
                static fn(array $action): string => $action['description'],
                $result['actions']
            );

            if (empty($details)) {
                Session::setFlash('success', 'Schema is already up to date.');
            } else {
                Session::setFlash('success', 'Schema upgrade complete: ' . implode('; ', $details));
            }
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Schema upgrade failed: ' . $e->getMessage());
        }

        $this->redirect('admin/settings');
    }

    public function sendTestEmail(Request $request): void {
        $email = trim((string) $request->post('test_email_to', ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Enter a valid recipient email for the test message.');
            $this->redirect('admin/settings');
            return;
        }

        $mailer = new \App\Services\MailService();
        $sent = $mailer->sendTestEmail($email);

        if ($sent) {
            Session::setFlash('success', 'Test email sent to ' . $email . '. Check that inbox (and spam folder).');
        } else {
            $detail = $mailer->getLastError();
            Session::setFlash(
                'error',
                'Test email failed.' . ($detail !== '' ? ' ' . $detail : ' Please verify your mail settings and try again.')
            );
        }

        $this->redirect('admin/settings');
    }

    private function writeDebugOverride(bool $enabled): bool {
        $path = base_path('config/debug.local.php');
        $contents = "<?php\nreturn [\n    'debug' => " . ($enabled ? 'true' : 'false') . ",\n];\n";
        return @file_put_contents($path, $contents) !== false;
    }

    public function exportLeads(Request $request): void {
        $leadModel = new Lead();
        $leads     = $leadModel->exportAll([
            'status' => $request->get('status', ''),
            'from'   => $request->get('from', ''),
            'to'     => $request->get('to', ''),
        ]);
        $exporter  = new CsvExporter();
        $csv       = $exporter->exportLeads($leads);
        $exporter->sendDownload($csv, 'leads-' . date('Y-m-d') . '.csv');
    }

    public function exportScans(Request $request): void {
        $scans = $this->db->fetchAll(
            "SELECT req.id, req.website_url, req.status, rep.overall_score, sc.seo_score, sc.accessibility_score, sc.conversion_score, sc.technical_score, req.requested_at, req.completed_at
             FROM audit_requests req
             LEFT JOIN audit_reports rep ON rep.audit_request_id = req.id
             LEFT JOIN audit_scores sc ON sc.audit_report_id = rep.id
             ORDER BY req.requested_at DESC"
        );
        $exporter = new CsvExporter();
        $csv      = $exporter->exportScans($scans);
        $exporter->sendDownload($csv, 'scans-' . date('Y-m-d') . '.csv');
    }
}
