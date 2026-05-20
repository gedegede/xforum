<?php
declare(strict_types=1);

namespace Lib;

use PDO;
use PDOStatement;

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private ?array $config = null;

    private function __construct() {
        $this->config = require ROOT_PATH . '/config/database.php';
        $this->connect();
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void {
        $default = $this->config['default'];
        $connection = $this->config['connections'][$default];

        if ($connection['driver'] === 'sqlite') {
            $this->connection = new PDO('sqlite:' . $connection['database']);
        } elseif ($connection['driver'] === 'mysql') {
            $dsn = "mysql:host={$connection['host']};dbname={$connection['database']};charset={$connection['charset']}";
            $this->connection = new PDO($dsn, $connection['username'], $connection['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$connection['charset']}",
            ]);
        }

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private static function getConnection(): PDO {
        return self::getInstance()->connection;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int)self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $params = []): int {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = self::query($sql, array_merge($data, $params));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function count(string $table, string $where = '', array $params = []): int {
        $sql = "SELECT COUNT(*) as count FROM " . $table . ($where ? " WHERE {$where}" : '');
        $result = self::fetch($sql, $params);
        return (int)($result['count'] ?? 0);
    }
}
?>