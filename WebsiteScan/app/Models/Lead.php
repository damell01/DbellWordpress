<?php
namespace App\Models;

class Lead extends Model {
    protected string $table = 'leads';

    public function recent(int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function search(string $term, int $page = 1, int $perPage = 20): array {
        $like = '%' . $term . '%';
        return $this->paginate($page, $perPage,
            "contact_name LIKE ? OR email LIKE ? OR business_name LIKE ? OR website_url LIKE ?",
            [$like, $like, $like, $like]
        );
    }

    public function byStatus(string $status): array {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE status = ? ORDER BY created_at DESC",
            [$status]
        );
    }

    public function countThisMonth(): int {
        return (int) $this->db->scalar(
            "SELECT COUNT(*) FROM `{$this->table}` WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
    }

    public function exportAll(array $filters = []): array {
        $where  = [];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['from'])) {
            $where[]  = 'created_at >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[]  = 'created_at <= ?';
            $params[] = $filters['to'] . ' 23:59:59';
        }
        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC';
        return $this->db->fetchAll($sql, $params);
    }
}
