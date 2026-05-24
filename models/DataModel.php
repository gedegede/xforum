<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class DataModel {
    const TABLE = 'next_data';
    const PRIMARY_KEY = 'dkey';

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return $cache;
        }

        $data = Database::fetchAll("SELECT * FROM " . self::TABLE);
        $result = [];
        foreach ($data as $row) {
            $result[$row['dkey']] = $row['val'];
        }
        CacheHelper::setCache(self::TABLE, $result);
        return $result;
    }

    public static function get(string $key, string $default = ''): string {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int {
        $value = self::get($key);
        return $value === '' ? $default : (int)$value;
    }

    public static function set(string $key, string $value): void {
        $exists = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE dkey = :dkey", ['dkey' => $key]);
        if ($exists) {
            Database::query("UPDATE " . self::TABLE . " SET val = :val WHERE dkey = :dkey", ['val' => $value, 'dkey' => $key]);
        } else {
            Database::query("INSERT INTO " . self::TABLE . " (dkey, val) VALUES (:dkey, :val)", ['dkey' => $key, 'val' => $value]);
        }
        CacheHelper::deleteCache(self::TABLE);
    }

    public static function setInt(string $key, int $value): void {
        self::set($key, (string)$value);
    }

    public static function increment(string $key, int $delta = 1): int {
        $current = self::getInt($key);
        $current += $delta;
        if ($current < 0) $current = 0;
        self::setInt($key, $current);
        return $current;
    }

    public static function delete(string $key): int {
        $result = Database::delete(self::TABLE, self::PRIMARY_KEY . " = :dkey", ['dkey' => $key]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function updateCount(string $key, int $delta): int {
        return self::increment($key, $delta);
    }
}
?>