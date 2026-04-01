<?php
namespace App\Models;

class AuditRequest extends Model {
    protected string $table = 'audit_requests';

    public function recent(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY requested_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function countByDay(int $days = 30): array {
        return $this->db->fetchAll(
            "SELECT DATE(requested_at) as day, COUNT(*) as total
             FROM `{$this->table}`
             WHERE requested_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(requested_at)
             ORDER BY day ASC",
            [$days]
        );
    }

    public function topDomains(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT normalized_url, COUNT(*) as cnt FROM `{$this->table}` GROUP BY normalized_url ORDER BY cnt DESC LIMIT ?",
            [$limit]
        );
    }

    public function checkRateLimit(string $ip, int $limit, int $windowSeconds): bool {
        $windowSeconds = max(60, $windowSeconds);
        $count = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE ip_address = ? AND requested_at >= DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)",
            [$ip]
        );
        return $count < $limit;
    }

    public function updateStatus(int $id, string $status, ?string $completedAt = null): void {
        $data = ['status' => $status];
        if ($completedAt) $data['completed_at'] = $completedAt;
        $this->update($id, $data);
    }
}
