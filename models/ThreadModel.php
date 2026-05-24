<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\ViewCounter;

class ThreadModel {
    const TABLE = 'next_thread';
    const PRIMARY_KEY = 'tid';
    private const PAGE_SIZE = 20;
    private const FILTER_BATCH_SIZE = 100;
    
    private static array $memoryCache = [];

    public static function getThreads(int $fid, int $page = 1, string $order = 'tid', string $keyword = ''): array {
        [$sql, $params] = self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread): bool {
                return self::isApproved($thread);
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }
    
    public static function getThreadCount(int $fid, string $keyword = ''): int {
        if ($keyword === '') {
            $forum = ForumModel::get($fid);
            return (int)($forum['thread_num'] ?? 0);
        }

        [$sql, $params] = self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword, 'tid, sort_order');
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread): bool {
                return self::isApproved($thread);
            }
        );
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
            $result = ViewCounter::applyPendingToThread($result);
            self::$memoryCache[$tid] = $result;
        }
        return $result;
    }

    public static function getUserThreads(int $uid, int $page = 1): array {
        $threads = Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY tid DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid],
            static function (array $thread): bool {
                return self::isApproved($thread);
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );

        return ViewCounter::applyPendingToThreads($threads);
    }

    public static function getUserThreadCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['thread_num'] ?? 0);
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
        if (!ViewCounter::increment($tid)) {
            Database::query("UPDATE " . self::TABLE . " SET view_num = view_num + 1 WHERE tid = :tid", ['tid' => $tid]);
        }
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

    public static function search(int $page = 1, string $order = 'tid', int $fid = 0, int $uid = 0, string $keyword = ''): array {
        [$sql, $params] = self::buildIndexedListQuery($fid, $uid, $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread) use ($fid, $uid): bool {
                return self::matchesThreadFilters($thread, $fid, $uid, false);
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function searchCount(int $fid = 0, int $uid = 0, string $keyword = ''): int {
        [$sql, $params] = self::buildIndexedListQuery($fid, $uid, $keyword, 'tid, fid, uid, sort_order');
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread) use ($fid, $uid): bool {
                return self::matchesThreadFilters($thread, $fid, $uid, false);
            }
        );
    }

    public static function getHomeThreads(int $limit = 30): array {
        $threads = Database::fetchFilteredLimit(
            "SELECT * FROM " . self::TABLE . " ORDER BY tid DESC LIMIT :limit OFFSET :offset",
            [],
            static function (array $thread): bool {
                return self::isApproved($thread);
            },
            $limit,
            self::FILTER_BATCH_SIZE
        );

        return ViewCounter::applyPendingToThreads($threads);
    }

    public static function getHomeThreadsWithFilter(int $page = 1, string $order = 'tid', string $keyword = ''): array {
        [$sql, $params] = self::buildListQuery([], [], $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread): bool {
                return self::isApproved($thread);
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function getHomeThreadCount(string $keyword = ''): int {
        if ($keyword === '') {
            return max(0, self::count() - DataModel::getInt('pending_threads'));
        }

        [$sql, $params] = self::buildListQuery([], [], $keyword, 'tid, sort_order');
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread): bool {
                return self::isApproved($thread);
            }
        );
    }

    public static function getCollapsedThreads(int $page = 1, string $order = 'tid', string $keyword = '', array $includeFids = []): array {
        $includeMap = array_flip(array_map('intval', $includeFids));
        [$sql, $params] = self::buildListQuery([], [], $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread) use ($includeMap): bool {
                return self::isApproved($thread)
                    && (empty($includeMap) || isset($includeMap[(int)$thread['fid']]));
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function getCollapsedThreadCount(string $keyword = '', array $includeFids = []): int {
        $includeMap = array_flip(array_map('intval', $includeFids));
        [$sql, $params] = self::buildListQuery([], [], $keyword, 'tid, fid, sort_order');
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread) use ($includeMap): bool {
                return self::isApproved($thread)
                    && (empty($includeMap) || isset($includeMap[(int)$thread['fid']]));
            }
        );
    }

    public static function getPendingApproveCount(): int {
        return DataModel::getInt('pending_threads');
    }

    public static function getThreadsByTids(array $tids): array {
        $tids = array_values(array_filter(array_unique(array_map('intval', $tids))));
        if (empty($tids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tids), '?'));
        $sql = "SELECT tid, subject, fid, reply_num, view_num, dateline FROM " . self::TABLE . " WHERE tid IN ($placeholders)";
        $threads = Database::fetchAll($sql, $tids);

        return array_column(ViewCounter::applyPendingToThreads($threads), null, 'tid');
    }

    public static function getHotThreadsByFid(int $fid, int $limit = 5, int $excludeTid = 0): array {
        $threads = Database::fetchAll(
            "SELECT tid, subject, fid, reply_num, view_num, dateline, sort_order FROM " . self::TABLE . " WHERE fid = :fid ORDER BY tid DESC LIMIT :limit",
            ['fid' => $fid, 'limit' => max($limit, 20)]
        );

        $threads = array_values(array_filter($threads, static function (array $thread) use ($excludeTid): bool {
            return self::isApproved($thread) && (int)$thread['tid'] !== $excludeTid;
        }));

        return array_slice(self::sortThreads(ViewCounter::applyPendingToThreads($threads), 'reply_num'), 0, $limit);
    }

    private static function isApproved(array $thread): bool {
        return (int)($thread['sort_order'] ?? 0) >= 0;
    }

    private static function matchesThreadFilters(array $thread, int $fid, int $uid, bool $approvedOnly): bool {
        if ($approvedOnly && !self::isApproved($thread)) {
            return false;
        }
        if ($fid > 0 && (int)$thread['fid'] !== $fid) {
            return false;
        }
        if ($uid > 0 && (int)$thread['uid'] !== $uid) {
            return false;
        }
        return true;
    }

    private static function buildIndexedListQuery(int $fid, int $uid, string $keyword = '', string $columns = '*'): array {
        if ($fid > 0) {
            return self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword, $columns);
        }

        if ($uid > 0) {
            return self::buildListQuery(['uid = :uid'], ['uid' => $uid], $keyword, $columns);
        }

        return self::buildListQuery([], [], $keyword, $columns);
    }

    private static function buildListQuery(array $where, array $params, string $keyword = '', string $columns = '*'): array {
        $keyword = trim($keyword);
        if ($keyword !== '') {
            $where[] = "subject LIKE :keyword ESCAPE '~'";
            $params['keyword'] = '%' . self::escapeLikeKeyword($keyword) . '%';
        }

        $whereSql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
        return [
            "SELECT {$columns} FROM " . self::TABLE . $whereSql . " ORDER BY tid DESC LIMIT :limit OFFSET :offset",
            $params,
        ];
    }

    private static function escapeLikeKeyword(string $keyword): string {
        return strtr($keyword, [
            '~' => '~~',
            '%' => '~%',
            '_' => '~_',
        ]);
    }
    
    private static function sortThreads(array $threads, string $order): array {
        if ($order === 'tid' || empty($threads)) {
            return $threads;
        }
        
        usort($threads, function($a, $b) use ($order) {
            $valA = $a[$order] ?? 0;
            $valB = $b[$order] ?? 0;
            
            if ($valA == $valB) {
                return ((int)($b['tid'] ?? 0)) <=> ((int)($a['tid'] ?? 0));
            }
            
            return $valA < $valB ? 1 : -1;
        });
        
        return $threads;
    }
}
?>
