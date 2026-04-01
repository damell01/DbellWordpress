<?php
namespace App\Models;

class Setting extends Model {
    protected string $table = 'settings';
    private static array $cache = [];

    public function get(string $key, mixed $default = null): mixed {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $row = $this->findBy('setting_key', $key);
        $value = $row ? $row['setting_value'] : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public function set(string $key, mixed $value): void {
        self::$cache[$key] = $value;
        $existing = $this->findBy('setting_key', $key);
        if ($existing) {
            $this->update($existing['id'], ['setting_value' => $value]);
        } else {
            $this->create(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function setMany(array $settings): void {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getAll(): array {
        $rows   = $this->all();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function getGroup(string $prefix): array {
        $rows   = $this->db->fetchAll("SELECT * FROM `{$this->table}` WHERE setting_key LIKE ?", ["{$prefix}%"]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }
}
