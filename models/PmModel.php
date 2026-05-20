<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class PmModel {
    const TABLE = 'next_pm';
    const PRIMARY_KEY = 'pmid';

    public static function send(int $uid, int $toUid, string $content): int {
        Database::query("UPDATE next_member SET inbox_num = inbox_num + 1 WHERE uid = :uid", ['uid' => $toUid]);
        return Database::insert(self::TABLE, [
            'uid' => $uid,
            'to_uid' => $toUid,
            'content' => $content,
            'dateline' => time(),
            'status' => 0,
            'is_read' => 0,
        ]);
    }

    public static function getInbox(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE to_uid = :uid ORDER BY pmid DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getInboxCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE to_uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function getOutbox(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY pmid DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getOutboxCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function get(int $pmid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :pmid", ['pmid' => $pmid]);
    }

    public static function markAsRead(int $uid): void {
        Database::query("UPDATE " . self::TABLE . " SET is_read = 1 WHERE to_uid = :uid AND is_read = 0", ['uid' => $uid]);
        Database::query("UPDATE next_member SET inbox_num = 0 WHERE uid = :uid", ['uid' => $uid]);
    }

    public static function markSingleAsRead(int $pmid): void {
        Database::query("UPDATE " . self::TABLE . " SET is_read = 1 WHERE " . self::PRIMARY_KEY . " = :pmid", ['pmid' => $pmid]);
    }

    public static function getUnreadCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE to_uid = :uid AND is_read = 0", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }
}
?>