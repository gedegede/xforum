<?php
declare(strict_types=1);

namespace Models;

use Lib\Database;

class ThreadModel {
    const TABLE = 'next_thread';
    const PRIMARY_KEY = 'tid';

    public static function getThreads(int $fid, int $page = 1, string $order = 'reply_time', string $keyword = ''): array {
        $offset = ($page - 1) * 20;
        
        $orderMap = [
            'reply_time' => 'reply_time DESC',
            'dateline' => 'dateline DESC',
            'reply_num' => 'reply_num DESC',
            'view_num' => 'view_num DESC'
        ];
        
        $orderBy = isset($orderMap[$order]) ? $orderMap[$order] : 'reply_time DESC';
        
        if (!empty($keyword)) {
            return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = :fid AND subject LIKE :keyword ORDER BY $orderBy LIMIT 20 OFFSET :offset", ['fid' => $fid, 'keyword' => '%' . $keyword . '%', 'offset' => $offset]);
        }
        
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = :fid ORDER BY $orderBy LIMIT 20 OFFSET :offset", ['fid' => $fid, 'offset' => $offset]);
    }
    
    public static function getThreadCount(int $fid, string $keyword = ''): int {
        if (!empty($keyword)) {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE fid = :fid AND subject LIKE :keyword", ['fid' => $fid, 'keyword' => '%' . $keyword . '%']);
        } else {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE fid = :fid", ['fid' => $fid]);
        }
        return (int)($result['count'] ?? 0);
    }

    public static function get(int $tid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
    }

    public static function getUserThreads(int $uid, int $page = 1): array {
        $offset = ($page - 1) * 20;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY tid DESC LIMIT 20 OFFSET :offset", ['uid' => $uid, 'offset' => $offset]);
    }

    public static function getUserThreadCount(int $uid): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE uid = :uid", ['uid' => $uid]);
        return (int)($result['count'] ?? 0);
    }

    public static function create(array $data): int {
        $data['dateline'] = time();
        $data['reply_time'] = time();
        $data['hash'] = md5(uniqid());
        return Database::insert(self::TABLE, $data);
    }

    public static function update(int $tid, array $data): int {
        $data['tid'] = $tid;
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :tid");
    }

    public static function delete(int $tid): int {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
    }

    public static function count(): int {
        return Database::count(self::TABLE);
    }

    public static function updateReply(int $tid, int $uid): void {
        Database::query("UPDATE " . self::TABLE . " SET reply_time = :time, reply_uid = :uid, reply_num = reply_num + 1 WHERE tid = :tid", ['time' => time(), 'uid' => $uid, 'tid' => $tid]);
    }

    public static function incrementView(int $tid): void {
        Database::query("UPDATE " . self::TABLE . " SET view_num = view_num + 1 WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function search(string $whereStr = '', array $params = [], int $page = 1): array {
        $offset = ($page - 1) * 20;
        $sql = "SELECT * FROM " . self::TABLE . " $whereStr ORDER BY dateline DESC LIMIT 20 OFFSET :offset";
        $params['offset'] = $offset;
        return Database::fetchAll($sql, $params);
    }

    public static function searchCount(string $whereStr = '', array $params = []): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return (int)($result['count'] ?? 0);
    }

    public static function getHomeThreads(int $limit = 30): array {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY tid DESC LIMIT :limit", ['limit' => $limit]);
    }

    public static function getHomeThreadsWithFilter(int $page = 1, string $order = 'reply_time', string $keyword = ''): array {
        $offset = ($page - 1) * 20;
        
        $orderMap = [
            'reply_time' => 'reply_time DESC',
            'dateline' => 'dateline DESC',
            'reply_num' => 'reply_num DESC',
            'view_num' => 'view_num DESC'
        ];
        
        $orderBy = isset($orderMap[$order]) ? $orderMap[$order] : 'reply_time DESC';
        
        if (!empty($keyword)) {
            return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE subject LIKE :keyword ORDER BY $orderBy LIMIT 20 OFFSET :offset", ['keyword' => '%' . $keyword . '%', 'offset' => $offset]);
        }
        
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY $orderBy LIMIT 20 OFFSET :offset", ['offset' => $offset]);
    }

    public static function getHomeThreadCount(string $keyword = ''): int {
        if (!empty($keyword)) {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE subject LIKE :keyword", ['keyword' => '%' . $keyword . '%']);
        } else {
            $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE);
        }
        return (int)($result['count'] ?? 0);
    }

    public static function getThreadsByTids(array $tids): array {
        $tids = array_values(array_filter(array_unique(array_map('intval', $tids))));
        if (empty($tids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tids), '?'));
        $sql = "SELECT tid, subject, fid, reply_num, view_num, dateline FROM " . self::TABLE . " WHERE tid IN ($placeholders)";
        $threads = Database::fetchAll($sql, $tids);

        return array_column($threads, null, 'tid');
    }
}
?>