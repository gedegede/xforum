<?php
class ModLogModel {
    const TABLE = 'next_mod_log';
    const PRIMARY_KEY = 'did';

    public static function addLog($uid, $action, $message) {
        Database::insert(self::TABLE, [
            'uid' => $uid,
            'message' => $message,
            'dateline' => time(),
        ]);
    }

    public static function getLogs($page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY did DESC LIMIT 20 OFFSET :offset", ['offset' => $offset]);
    }

    public static function getCount() {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE);
        return $result['count'] ?? 0;
    }
}
?>
