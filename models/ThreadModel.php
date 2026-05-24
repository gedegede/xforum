<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class ThreadModel {
    const TABLE = 'next_thread';
    const PRIMARY_KEY = 'tid';
    
    private static array $memoryCache = [];

    public static function getThreads(int $fid, int $page = 1, string $order = 'tid', string $keyword = ''): array {
        $offset = ($page - 1) * 20;
        
        if (!empty($keyword)) {
            $threads = Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = :fid AND subject LIKE :keyword ORDER BY tid DESC LIMIT 20 OFFSET :offset", ['fid' => $fid, 'keyword' => '%' . $keyword . '%', 'offset' => $offset]);
        } else {
            $threads = Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid = :fid ORDER BY tid DESC LIMIT 20 OFFSET :offset", ['fid' => $fid, 'offset' => $offset]);
        }
        
        return self::sortThreads($threads, $order);
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
        if ($tid <= 0) {
            return null;
        }
        
        if (isset(self::$memoryCache[$tid])) {
            return self::$memoryCache[$tid];
        }
        
        $result = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
        if ($result) {
            self::$memoryCache[$tid] = $result;
        }
        return $result;
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
        unset(self::$memoryCache[$tid]);
        $data['tid'] = $tid;
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :tid");
    }

    public static function delete(int $tid): int {
        unset(self::$memoryCache[$tid]);
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
    }

    public static function count(): int {
        return Database::count(self::TABLE);
    }

    public static function updateReply(int $tid, int $uid): void {
        unset(self::$memoryCache[$tid]);
        Database::query("UPDATE " . self::TABLE . " SET reply_time = :time, reply_uid = :uid, reply_num = reply_num + 1 WHERE tid = :tid", ['time' => time(), 'uid' => $uid, 'tid' => $tid]);
    }

    public static function incrementView(int $tid): void {
        unset(self::$memoryCache[$tid]);
        Database::query("UPDATE " . self::TABLE . " SET view_num = view_num + 1 WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function incrementFavNum(int $tid): void {
        if ($tid <= 0) return;
        unset(self::$memoryCache[$tid]);
        Database::query("UPDATE " . self::TABLE . " SET fav_num = fav_num + 1 WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function decrementFavNum(int $tid): void {
        if ($tid <= 0) return;
        unset(self::$memoryCache[$tid]);
        Database::query("UPDATE " . self::TABLE . " SET fav_num = CASE WHEN fav_num > 0 THEN fav_num - 1 ELSE 0 END WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function search(string $whereStr = '', array $params = [], int $page = 1, string $order = 'tid'): array {
        $offset = ($page - 1) * 20;
        $sql = "SELECT * FROM " . self::TABLE . " $whereStr ORDER BY tid DESC LIMIT 20 OFFSET :offset";
        $params['offset'] = $offset;
        $threads = Database::fetchAll($sql, $params);
        
        return self::sortThreads($threads, $order);
    }

    public static function searchCount(string $whereStr = '', array $params = []): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return (int)($result['count'] ?? 0);
    }

    public static function getHomeThreads(int $limit = 30): array {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY tid DESC LIMIT :limit", ['limit' => $limit]);
    }

    public static function getHomeThreadsWithFilter(int $page = 1, string $order = 'tid', string $keyword = ''): array {
        $offset = ($page - 1) * 20;
        
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = 'subject LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }
        
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params['offset'] = $offset;
        
        $threads = Database::fetchAll("SELECT * FROM " . self::TABLE . " $whereStr ORDER BY tid DESC LIMIT 20 OFFSET :offset", $params);
        
        return self::sortThreads($threads, $order);
    }

    public static function getHomeThreadCount(string $keyword = ''): int {
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = 'subject LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }
        
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return (int)($result['count'] ?? 0);
    }

    public static function getCollapsedThreads(int $page = 1, string $order = 'tid', string $keyword = '', array $includeFids = []): array {
        $offset = ($page - 1) * 20;
        
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = 'subject LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }
        
        if (!empty($includeFids)) {
            $placeholders = implode(',', array_fill(0, count($includeFids), '?'));
            $where[] = "fid IN ($placeholders)";
            $params = array_merge($params, $includeFids);
        }
        
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params['offset'] = $offset;
        
        $threads = Database::fetchAll("SELECT * FROM " . self::TABLE . " $whereStr ORDER BY tid DESC LIMIT 20 OFFSET :offset", $params);
        
        return self::sortThreads($threads, $order);
    }

    public static function getCollapsedThreadCount(string $keyword = '', array $includeFids = []): int {
        $where = [];
        $params = [];
        
        if (!empty($keyword)) {
            $where[] = 'subject LIKE :keyword';
            $params['keyword'] = '%' . $keyword . '%';
        }
        
        if (!empty($includeFids)) {
            $placeholders = implode(',', array_fill(0, count($includeFids), '?'));
            $where[] = "fid IN ($placeholders)";
            $params = array_merge($params, $includeFids);
        }
        
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $whereStr", $params);
        return (int)($result['count'] ?? 0);
    }

    public static function getPendingApproveCount(): int {
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE sort_order = -1");
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
    
    private static function sortThreads(array $threads, string $order): array {
        if ($order === 'tid' || empty($threads)) {
            return $threads;
        }
        
        usort($threads, function($a, $b) use ($order) {
            $valA = $a[$order] ?? 0;
            $valB = $b[$order] ?? 0;
            
            if ($valA == $valB) {
                return 0;
            }
            
            return $valA < $valB ? 1 : -1;
        });
        
        return $threads;
    }
}
?>