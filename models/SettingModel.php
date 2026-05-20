<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class SettingModel {
    const TABLE = 'next_setting';
    const PRIMARY_KEY = 'skey';

    public static function get(string $key, string $default = ''): string {
        $result = Database::fetch("SELECT val FROM " . self::TABLE . " WHERE skey = :skey", ['skey' => $key]);
        return $result ? $result['val'] : $default;
    }

    public static function set(string $key, string $value): void {
        $exists = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE skey = :skey", ['skey' => $key]);
        if ($exists) {
            Database::query("UPDATE " . self::TABLE . " SET val = :val WHERE skey = :skey", ['val' => $value, 'skey' => $key]);
        } else {
            Database::query("INSERT INTO " . self::TABLE . " (skey, val) VALUES (:skey, :val)", ['skey' => $key, 'val' => $value]);
        }
    }

    public static function getAll(): array {
        $result = Database::fetchAll("SELECT * FROM " . self::TABLE);
        $settings = [];
        foreach ($result as $row) {
            $settings[$row['skey']] = $row['val'];
        }
        return $settings;
    }
}
?>