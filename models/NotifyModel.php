<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class NotifyModel {
    const TABLE = 'next_notify';
    const PRIMARY_KEY = 'did';

    public static function getNotifies(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY did DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getNotifyCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function getUnreadCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid AND status = 0", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function markAsRead(int $uid): void {
        Database::query("UPDATE " . self::TABLE . " SET status = 1 WHERE uid = :uid AND status = 0", ['uid' => $uid]);
        Database::query("UPDATE next_member SET notify_num = 0 WHERE uid = :uid", ['uid' => $uid]);
    }

    public static function addNotify(int $uid, int $fromUid, int $tid, int $pid, string $message): int {
        $existing = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid", ['uid' => $uid, 'tid' => $tid]);

        if ($existing) {
            Database::query("UPDATE " . self::TABLE . " SET from_uid = :from_uid, pid = :pid, dateline = :dateline, message = :message WHERE did = :did",
                ['from_uid' => $fromUid, 'pid' => $pid, 'dateline' => time(), 'message' => $message, 'did' => $existing['did']]);
            return $existing['did'];
        }

        Database::query("UPDATE next_member SET notify_num = notify_num + 1 WHERE uid = :uid", ['uid' => $uid]);
        return Database::insert(self::TABLE, [
            'uid' => $uid,
            'from_uid' => $fromUid,
            'tid' => $tid,
            'pid' => $pid,
            'dateline' => time(),
            'status' => 0,
            'message' => $message,
        ]);
    }

    public static function addPMNotify(int $uid, int $fromUid): int {
        Database::query("UPDATE next_member SET notify_num = notify_num + 1 WHERE uid = :uid", ['uid' => $uid]);
        return Database::insert(self::TABLE, [
            'uid' => $uid,
            'from_uid' => $fromUid,
            'tid' => 0,
            'pid' => 0,
            'dateline' => time(),
            'status' => 0,
            'message' => '你收到了一条新私信',
        ]);
    }
}
?>