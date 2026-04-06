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

    public function search(string $query, int $page = 1, int $perPage = 20, string $status = ''): array {
        $conditions = ["(name LIKE ? OR email LIKE ? OR message LIKE ? OR company LIKE ?)"];
        $params = ["%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%"];

        if ($status !== '') {
            $conditions[] = 'status = ?';
            $params[] = $status;
        }

        $where = implode(' AND ', $conditions);
        return $this->paginate($page, $perPage, $where, $params, 'created_at DESC');
    }

    public function filterByStatus(string $status, int $page = 1, int $perPage = 20): array {
        return $this->paginate($page, $perPage, 'status = ?', [$status], 'created_at DESC');
    }

    public function updateStatus(int $id, string $status): int {
        return $this->db->update($this->table, ['status' => $status], ['id' => $id]);
    }

    public function addNote(int $id, string $note): int {
        return $this->db->update($this->table, ['notes' => $note], ['id' => $id]);
    }

    public function withLead(int $id): ?array {
        return $this->db->fetch(
            "SELECT cr.*, l.status AS lead_status, l.business_name, l.website_url AS lead_website,
                    l.follow_up_stage, l.service_interest, l.source AS lead_source
             FROM `{$this->table}` cr
             LEFT JOIN leads l ON l.id = cr.lead_id
             WHERE cr.id = ?",
            [$id]
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

    public function exportAll(array $filters = []): array {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['from'] . ' 00:00:00';
        }
        if (!empty($filters['to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['to'] . ' 23:59:59';
        }

        $sql = "SELECT * FROM `{$this->table}`" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC';
        return $this->db->fetchAll($sql, $params);
    }

    public function countByStatus(): array {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) as cnt FROM `{$this->table}` GROUP BY status"
        );
        $counts = ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        return $counts;
    }
}
