<?php
namespace App\Core;

class Database {
    private static ?Database $instance = null;
    private \PDO $pdo;
    private string $host;
    private string $port;
    private string $name;
    private string $user;
    private string $pass;
    private string $charset = 'utf8mb4';

    private function __construct() {
        $this->host = (string) env('DB_HOST', 'localhost');
        $this->port = (string) env('DB_PORT', '3306');
        $this->name = (string) env('DB_NAME', 'sitescope');
        $this->user = (string) env('DB_USER', 'root');
        $this->pass = (string) env('DB_PASS', '');
        $this->connect();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo(): \PDO {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (!$this->shouldReconnect($e)) {
                throw $e;
            }

            $this->connect();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
    }

    public function fetch(string $sql, array $params = []): ?array {
        return $this->query($sql, $params)->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int {
        $cols    = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $holders = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO `{$table}` ({$cols}) VALUES ({$holders})", array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int {
        $set   = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $cond  = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));
        $stmt  = $this->query(
            "UPDATE `{$table}` SET {$set} WHERE {$cond}",
            [...array_values($data), ...array_values($where)]
        );
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int {
        $cond = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));
        return $this->query("DELETE FROM `{$table}` WHERE {$cond}", array_values($where))->rowCount();
    }

    public function count(string $table, array $where = []): int {
        $cond   = '';
        $params = [];
        if ($where) {
            $cond   = ' WHERE ' . implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));
            $params = array_values($where);
        }
        return (int)$this->query("SELECT COUNT(*) FROM `{$table}`{$cond}", $params)->fetchColumn();
    }

    public function scalar(string $sql, array $params = []): mixed {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function lastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollback(): void         { $this->pdo->rollBack(); }

    private function connect(): void {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->name};charset={$this->charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new \PDO($dsn, $this->user, $this->pass, $options);
    }

    private function shouldReconnect(\PDOException $e): bool {
        $message = strtolower($e->getMessage());
        $errorCode = $e->errorInfo[1] ?? null;

        return in_array($errorCode, [2006, 2013], true)
            || str_contains($message, 'server has gone away')
            || str_contains($message, 'lost connection');
    }
}
