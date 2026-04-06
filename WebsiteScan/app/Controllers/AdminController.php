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

        $audits = $this->db->fetchAll(
            "SELECT ar.*, rep.report_token, rep.overall_score FROM audit_requests ar LEFT JOIN audit_reports rep ON rep.audit_request_id = ar.id WHERE ar.lead_id = ? ORDER BY ar.requested_at DESC",
            [$id]
        );
        $notes = $this->db->fetchAll(
            "SELECT ln.*, u.name AS admin_name FROM lead_notes ln LEFT JOIN users u ON u.id = ln.user_id WHERE ln.lead_id = ? ORDER BY ln.created_at DESC",
            [$id]
        );
        $this->adminView('lead-detail', ['title' => 'Lead: ' . ($lead['contact_name'] ?: $lead['email']), 'lead' => $lead, 'audits' => $audits, 'notes' => $notes]);
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
            $this->db->insert('lead_notes', [
                'lead_id'    => $id,
                'user_id'    => $userId ?: null,
                'note'       => $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            Session::setFlash('success', 'Note added.');
        }
        $this->redirect("admin/leads/{$id}");
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
