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
    const TABLE_DIALOG = 'next_pm_dialog';
    public const PAGE_SIZE = 20;

    public static function send(int $uid, int $toUid, string $content): int {
        MemberModel::incrementInboxNum($toUid);
        $dialogKey = self::dialogKey($uid, $toUid);
        $time = time();
        $pmid = Database::insert(self::TABLE, [
            'dialog_key' => $dialogKey,
            'uid' => $uid,
            'to_uid' => $toUid,
            'content' => $content,
            'dateline' => $time,
        ]);
        self::upsertDialog($uid, $toUid, $pmid, $time, 0);
        self::upsertDialog($toUid, $uid, $pmid, $time, 1);
        return $pmid;
    }

    public static function getConversations(int $uid, int $page = 1): array {
        $offset = (max(1, $page) - 1) * self::PAGE_SIZE;
        $dialogs = Database::fetchAll(
            "SELECT * FROM " . self::TABLE_DIALOG . " WHERE uid = :uid ORDER BY last_pmid DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid, 'limit' => self::PAGE_SIZE, 'offset' => $offset]
        );
        $pmids = array_values(array_filter(array_map('intval', array_column($dialogs, 'last_pmid'))));
        if (empty($pmids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($pmids), '?'));
        $rows = array_column(Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE pmid IN ($placeholders)", $pmids), null, 'pmid');
        $conversations = [];
        foreach ($dialogs as $dialog) {
            $pmid = (int)$dialog['last_pmid'];
            if (!isset($rows[$pmid])) {
                continue;
            }
            $row = $rows[$pmid];
            $row['partner_uid'] = (int)$dialog['peer_uid'];
            $row['unread_num'] = (int)$dialog['unread_num'];
            $conversations[] = $row;
        }
        return $conversations;
    }

    public static function getConversationCount(int $uid): int {
        return Database::count(self::TABLE_DIALOG, 'uid = :uid', ['uid' => $uid]);
    }

    public static function getDialog(int $uid, int $partnerUid): array {
        return Database::fetch(
            "SELECT * FROM " . self::TABLE_DIALOG . " WHERE uid = :uid AND peer_uid = :peer_uid",
            ['uid' => $uid, 'peer_uid' => $partnerUid]
        ) ?: [];
    }

    public static function getConversation(int $uid, int $partnerUid, int $page = 1): array {
        $offset = (max(1, $page) - 1) * self::PAGE_SIZE;
        return Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE dialog_key = :dialog_key ORDER BY pmid ASC LIMIT :limit OFFSET :offset",
            ['dialog_key' => self::dialogKey($uid, $partnerUid), 'limit' => self::PAGE_SIZE, 'offset' => $offset]
        );
    }

    public static function getConversationAfter(int $uid, int $partnerUid, int $afterPmid): array {
        return Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE dialog_key = :dialog_key AND pmid > :pmid ORDER BY pmid ASC",
            ['dialog_key' => self::dialogKey($uid, $partnerUid), 'pmid' => $afterPmid]
        );
    }

    public static function markConversationAsRead(int $uid, int $partnerUid): void {
        Database::query(
            "UPDATE " . self::TABLE_DIALOG . " SET unread_num = 0 WHERE uid = :uid AND peer_uid = :peer_uid",
            ['uid' => $uid, 'peer_uid' => $partnerUid]
        );
        self::syncInboxNum($uid);
    }

    private static function dialogKey(int $uid, int $toUid): string {
        $uids = [$uid, $toUid];
        sort($uids, SORT_NUMERIC);
        return $uids[0] . ':' . $uids[1];
    }

    private static function upsertDialog(int $uid, int $peerUid, int $pmid, int $time, int $unread): void {
        Database::query(
            "INSERT INTO " . self::TABLE_DIALOG . " (uid, peer_uid, last_pmid, last_time, pm_num, unread_num)
             VALUES (:uid, :peer_uid, :pmid, :time, 1, :unread)
             ON DUPLICATE KEY UPDATE last_pmid = VALUES(last_pmid), last_time = VALUES(last_time), pm_num = pm_num + 1, unread_num = unread_num + VALUES(unread_num)",
            [
                'uid' => $uid,
                'peer_uid' => $peerUid,
                'pmid' => $pmid,
                'time' => $time,
                'unread' => $unread,
            ]
        );
    }

    private static function syncInboxNum(int $uid): void {
        $unread = Database::fetch(
            "SELECT COALESCE(SUM(unread_num), 0) AS total FROM " . self::TABLE_DIALOG . " WHERE uid = :uid",
            ['uid' => $uid]
        );
        Database::query("UPDATE " . MemberModel::TABLE . " SET inbox_num = :num WHERE uid = :uid", ['num' => (int)($unread['total'] ?? 0), 'uid' => $uid]);
    }
}
?>
