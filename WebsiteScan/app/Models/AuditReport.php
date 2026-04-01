<?php
namespace App\Models;

class AuditReport extends Model {
    protected string $table = 'audit_reports';

    public function findByToken(string $token): ?array {
        return $this->findBy('report_token', $token);
    }

    public function findFullReport(int $id): ?array {
        $report = $this->find($id);
        if (!$report) return null;

        $report['pagespeed_mobile'] = $this->decodeJsonField($report['pagespeed_mobile_json'] ?? null);
        $report['pagespeed_desktop'] = $this->decodeJsonField($report['pagespeed_desktop_json'] ?? null);

        $report['scores'] = $this->db->fetch(
            "SELECT * FROM audit_scores WHERE audit_report_id = ?",
            [$id]
        );
        $report['issues'] = $this->db->fetchAll(
            "SELECT * FROM audit_issues WHERE audit_report_id = ? ORDER BY FIELD(severity,'critical','high','medium','low','info'), category",
            [$id]
        );
        $report['request'] = $this->db->fetch(
            "SELECT * FROM audit_requests WHERE id = ?",
            [$report['audit_request_id']]
        );
        if ($report['request']) {
            $report['lead'] = $this->db->fetch(
                "SELECT * FROM leads WHERE id = ?",
                [$report['request']['lead_id'] ?? 0]
            );
        }
        return $report;
    }

    private function decodeJsonField(?string $value): ?array {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return null;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function findPreviousForRequest(int $auditRequestId, int $excludeReportId): ?array {
        $request = $this->db->fetch("SELECT normalized_url, website_url, lead_id FROM audit_requests WHERE id = ?", [$auditRequestId]);
        if (!$request) {
            return null;
        }

        return $this->db->fetch(
            "SELECT rep.*, req.website_url, req.normalized_url, req.requested_at
             FROM audit_reports rep
             JOIN audit_requests req ON req.id = rep.audit_request_id
             WHERE rep.id <> ?
               AND req.normalized_url = ?
             ORDER BY rep.created_at DESC
             LIMIT 1",
            [$excludeReportId, $request['normalized_url'] ?? $request['website_url'] ?? '']
        );
    }

    public function feedbackSummary(int $auditReportId): array {
        try {
            $rows = $this->db->fetchAll(
                "SELECT audit_issue_id, feedback_type, COUNT(*) AS total
                 FROM audit_issue_feedback
                 WHERE audit_report_id = ?
                 GROUP BY audit_issue_id, feedback_type",
                [$auditReportId]
            );
        } catch (\Throwable $e) {
            return [];
        }

        $summary = [];
        foreach ($rows as $row) {
            $issueId = (int) ($row['audit_issue_id'] ?? 0);
            $type = (string) ($row['feedback_type'] ?? '');
            if (!isset($summary[$issueId])) {
                $summary[$issueId] = ['incorrect' => 0, 'helpful' => 0];
            }
            if (isset($summary[$issueId][$type])) {
                $summary[$issueId][$type] = (int) ($row['total'] ?? 0);
            }
        }

        return $summary;
    }

    public function recentWithRequest(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT r.*, req.website_url, req.requested_at, req.status as req_status
             FROM audit_reports r
             JOIN audit_requests req ON req.id = r.audit_request_id
             ORDER BY r.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function scoreDistribution(): array {
        return $this->db->fetchAll(
            "SELECT
               SUM(CASE WHEN overall_score >= 80 THEN 1 ELSE 0 END) as good,
               SUM(CASE WHEN overall_score >= 50 AND overall_score < 80 THEN 1 ELSE 0 END) as fair,
               SUM(CASE WHEN overall_score < 50 THEN 1 ELSE 0 END) as poor
             FROM `{$this->table}`"
        );
    }

    public function commonIssues(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT code, title, category, COUNT(*) as cnt
             FROM audit_issues
             GROUP BY code, title, category
             ORDER BY cnt DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function incrementViews(int $id): void {
        $this->db->query(
            "INSERT INTO report_views (audit_report_id, viewed_at) VALUES (?, NOW())",
            [$id]
        );
    }
}
