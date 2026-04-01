<?php
namespace App\Models;

use App\Core\Database;

abstract class Model {
    protected string $table;
    protected Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE id = ?", [$id]);
    }

    public function findBy(string $column, mixed $value): ?array {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE `{$column}` = ?", [$value]);
    }

    public function all(string $orderBy = 'id DESC', int $limit = 1000): array {
        return $this->db->fetchAll("SELECT * FROM `{$this->table}` ORDER BY {$orderBy} LIMIT {$limit}");
    }

    public function create(array $data): int {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete(int $id): int {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function count(array $where = []): int {
        return $this->db->count($this->table, $where);
    }

    public function paginate(int $page, int $perPage, string $where = '', array $params = [], string $orderBy = 'id DESC'): array {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT * FROM `{$this->table}`" . ($where ? " WHERE {$where}" : '') . " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $items  = $this->db->fetchAll($sql, $params);
        $total  = (int) $this->db->scalar("SELECT COUNT(*) FROM `{$this->table}`" . ($where ? " WHERE {$where}" : ''), $params);
        return [
            'items'        => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
