<?php
class PmModel {
    const TABLE = 'next_pm';
    const PRIMARY_KEY = 'pmid';

    public static function send($uid, $toUid, $content) {
        Database::query("UPDATE next_member SET inbox_num = inbox_num + 1 WHERE uid = ?", [$toUid]);
        return Database::insert(self::TABLE, [
            'uid' => $uid,
            'to_uid' => $toUid,
            'content' => $content,
            'dateline' => time(),
            'status' => 0,
            'is_read' => 0,
        ]);
    }

    public static function getInbox($uid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE to_uid = ? ORDER BY pmid DESC LIMIT 20 OFFSET ?", [$uid, $offset]);
    }

    public static function getInboxCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE to_uid = ?", [$uid]);
        return $result['count'] ?? 0;
    }

    public static function getOutbox($uid, $page = 1) {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = ? ORDER BY pmid DESC LIMIT 20 OFFSET ?", [$uid, $offset]);
    }

    public static function getOutboxCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = ?", [$uid]);
        return $result['count'] ?? 0;
    }

    public static function get($pmid) {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = ?", [$pmid]);
    }

    public static function markAsRead($uid) {
        Database::query("UPDATE " . self::TABLE . " SET is_read = 1 WHERE to_uid = ? AND is_read = 0", [$uid]);
        Database::query("UPDATE next_member SET inbox_num = 0 WHERE uid = ?", [$uid]);
    }

    public static function markSingleAsRead($pmid) {
        Database::query("UPDATE " . self::TABLE . " SET is_read = 1 WHERE " . self::PRIMARY_KEY . " = ?", [$pmid]);
    }

    public static function getUnreadCount($uid) {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE to_uid = ? AND is_read = 0", [$uid]);
        return $result['count'] ?? 0;
    }
}
?>
