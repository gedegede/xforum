<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Models\MemberModel;

class PmModel {
    const TABLE = 'next_pm';
    const PRIMARY_KEY = 'pmid';

    public static function send(int $uid, int $toUid, string $content): int {
        MemberModel::incrementInboxNum($toUid);
        MemberModel::incrementOutboxNum($uid);
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
        MemberModel::resetInboxNum($uid);
    }

    public static function markSingleAsRead(int $pmid, int $uid): void {
        Database::query(
            "UPDATE " . self::TABLE . " SET is_read = 1 WHERE " . self::PRIMARY_KEY . " = :pmid AND to_uid = :uid",
            ['pmid' => $pmid, 'uid' => $uid]
        );
    }

    public static function getUnreadCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['inbox_num'] ?? 0);
    }
}
?>
