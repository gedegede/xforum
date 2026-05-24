<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Models\MemberModel;

class NotifyModel {
    const TABLE = 'next_notify';
    const PRIMARY_KEY = 'did';
    private const PAGE_SIZE = 20;
    private const FILTER_BATCH_SIZE = 100;

    public static function getNotifies(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY did DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getNotifyCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function getUnreadCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['notify_num'] ?? 0);
    }

    public static function markAsRead(int $uid): void {
        Database::query("UPDATE " . self::TABLE . " SET status = 1 WHERE uid = :uid AND status = 0", ['uid' => $uid]);
        MemberModel::resetNotifyNum($uid);
    }

    public static function addNotify(int $uid, int $fromUid, int $tid, int $pid, string $message): int {
        $existing = Database::fetch(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND tid = :tid LIMIT 1",
            ['uid' => $uid, 'tid' => $tid]
        );

        if ($existing) {
            Database::query("UPDATE " . self::TABLE . " SET from_uid = :from_uid, pid = :pid, dateline = :dateline, message = :message WHERE did = :did",
                ['from_uid' => $fromUid, 'pid' => $pid, 'dateline' => time(), 'message' => $message, 'did' => $existing['did']]);
            return $existing['did'];
        }

        MemberModel::incrementNotifyNum($uid);
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
        MemberModel::incrementNotifyNum($uid);
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
