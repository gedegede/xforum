<?php
class SettingModel {
    const TABLE = 'next_setting';
    const PRIMARY_KEY = 'skey';

    public static function get($key, $default = '') {
        $result = Database::fetch("SELECT val FROM " . self::TABLE . " WHERE skey = ?", [$key]);
        return $result ? $result['val'] : $default;
    }

    public static function set($key, $value) {
        $exists = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE skey = ?", [$key]);
        if ($exists) {
            Database::query("UPDATE " . self::TABLE . " SET val = ? WHERE skey = ?", [$value, $key]);
        } else {
            Database::query("INSERT INTO " . self::TABLE . " (skey, val) VALUES (?, ?)", [$key, $value]);
        }
    }

    public static function getAll() {
        $result = Database::fetchAll("SELECT * FROM " . self::TABLE);
        $settings = [];
        foreach ($result as $row) {
            $settings[$row['skey']] = $row['val'];
        }
        return $settings;
    }
}
?>