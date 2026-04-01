<?php
namespace App\Models;

class ContactRequest extends Model {
    protected string $table = 'contact_requests';

    public function recent(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function withAuditInfo(int $limit = 50): array {
        return $this->db->fetchAll(
            "SELECT cr.*, ar.report_token, ar.overall_score
             FROM `{$this->table}` cr
             LEFT JOIN audit_reports ar ON ar.id = cr.audit_report_id
             ORDER BY cr.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
