<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class ModLogModel {
    const TABLE = 'next_mod_log';
    const PRIMARY_KEY = 'did';

    public static function addLog(int $uid, string $action, string $message): void {
        Database::insert(self::TABLE, [
            'uid' => $uid,
            'message' => $message,
            'dateline' => time(),
        ]);
    }

    public static function getLogs(int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY did DESC LIMIT 20 OFFSET :offset", ['offset' => $offset]);
    }

    public static function getCount(): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE);
        return (int)($result['count'] ?? 0);
    }
}
?>