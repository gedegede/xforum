<?php
class Database {
    private static $instance = null;
    private $connection = null;
    private $config = null;

    private function __construct() {
        $this->config = require ROOT_PATH . '/config/database.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
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

    private static function getConnection() {
        return self::getInstance()->connection;
    }

    public static function query($sql, $params = []) {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = self::query($sql, $data);
        return self::getConnection()->lastInsertId();
    }

    public static function update($table, $data, $where, $params = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = self::query($sql, array_merge($data, $params));
        return $stmt->rowCount();
    }

    public static function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table}" . ($where ? " WHERE {$where}" : '');
        $result = self::fetch($sql, $params);
        return $result['count'] ?? 0;
    }
}
?>