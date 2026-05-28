<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use PDO;
use PDOStatement;

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private ?array $config = null;
    private static array $queryLog = [];

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
        $connection = $this->config;
        $dsn = "mysql:host={$connection['host']};dbname={$connection['database']};charset={$connection['charset']}";
        $initCmd = defined('Pdo\Mysql::ATTR_INIT_COMMAND') ? \Pdo\Mysql::ATTR_INIT_COMMAND : PDO::MYSQL_ATTR_INIT_COMMAND;
        $this->connection = new PDO($dsn, $connection['username'], $connection['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            $initCmd => "SET NAMES {$connection['charset']}",
        ]);

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private static function getConnection(): PDO {
        return self::getInstance()->connection;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $startTime = microtime(true);
        $stmt = self::getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(is_int($key) ? $key + 1 : ':' . $key, $value, $type);
        }
        $stmt->execute();
        $endTime = microtime(true);

        if (self::isDebugEnabled()) {
            $duration = ($endTime - $startTime) * 1000;
            $queryInfo = [
                'sql' => $sql,
                'params' => $params,
                'duration' => $duration,
                'explain' => null
            ];

            if (stripos(trim($sql), 'SELECT') === 0) {
                $explainSql = 'EXPLAIN ' . $sql;
                $explainStmt = self::getConnection()->prepare($explainSql);
                foreach ($params as $key => $value) {
                    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                    $explainStmt->bindValue(is_int($key) ? $key + 1 : ':' . $key, $value, $type);
                }
                $explainStmt->execute();
                $queryInfo['explain'] = $explainStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            self::$queryLog[] = $queryInfo;
        }

        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        return $result;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function fetchFilteredLimit(string $sql, array $params, callable $filter, int $limit, int $batchSize = 100): array {
        $limit = max(0, $limit);
        if ($limit === 0) {
            return [];
        }

        $batchSize = max($limit, $batchSize);
        $offset = 0;
        $result = [];

        do {
            $batchParams = $params;
            $batchParams['limit'] = $batchSize;
            $batchParams['offset'] = $offset;
            $rows = self::fetchAll($sql, $batchParams);
            $offset += count($rows);

            foreach ($rows as $row) {
                if ($filter($row)) {
                    $result[] = $row;
                    if (count($result) >= $limit) {
                        return $result;
                    }
                }
            }
        } while (count($rows) === $batchSize);

        return $result;
    }

    public static function fetchFilteredPage(string $sql, array $params, callable $filter, int $page, int $pageSize = 20, int $batchSize = 100): array {
        $page = max(1, $page);
        $pageSize = max(1, $pageSize);
        $needed = $page * $pageSize;
        $rows = self::fetchFilteredLimit($sql, $params, $filter, $needed, $batchSize);

        return array_slice($rows, ($page - 1) * $pageSize, $pageSize);
    }

    public static function countFiltered(string $sql, array $params, callable $filter, int $batchSize = 500): int {
        $batchSize = max(1, $batchSize);
        $offset = 0;
        $count = 0;

        do {
            $batchParams = $params;
            $batchParams['limit'] = $batchSize;
            $batchParams['offset'] = $offset;
            $rows = self::fetchAll($sql, $batchParams);
            $offset += count($rows);

            foreach ($rows as $row) {
                if ($filter($row)) {
                    $count++;
                }
            }
        } while (count($rows) === $batchSize);

        return $count;
    }

    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int)self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $params = []): int {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "`{$key}` = :{$key}";
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

    public static function beginTransaction(): void {
        $connection = self::getConnection();
        if (!$connection->inTransaction()) {
            $connection->beginTransaction();
        }
    }

    public static function commit(): void {
        $connection = self::getConnection();
        if ($connection->inTransaction()) {
            $connection->commit();
        }
    }

    public static function rollBack(): void {
        $connection = self::getConnection();
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
    }
    
    public static function getQueryLog(): array {
        return self::$queryLog;
    }

    public static function clearQueryLog(): void {
        self::$queryLog = [];
    }

    private static function isDebugEnabled(): bool {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        $config = require ROOT_PATH . '/config/app.php';
        $enabled = !empty($config['debug']);
        return $enabled;
    }
}
?>
