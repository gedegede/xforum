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
    private const HOT_THREADS_CACHE_TTL = 3600;
    private const HOME_NOTICE_CACHE_KEY = 'home_notice_threads';
    
    private static array $memoryCache = [];

    public static function getThreads(int $fid, int $page = 1, string $order = 'tid', string $keyword = '', int $pageSize = self::PAGE_SIZE): array {
        [$sql, $params] = self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread): bool {
                return true;
            },
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }
    
    public static function getThreadCount(int $fid, string $keyword = ''): int {
        if ($keyword === '') {
            $forum = ForumModel::get($fid);
            return (int)($forum['thread_num'] ?? 0);
        }

        [$sql, $params] = self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword);
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread): bool {
                return true;
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

    public static function getUserThreads(int $uid, int $page = 1, int $pageSize = self::PAGE_SIZE): array {
        $threads = Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY tid DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid],
            static function (array $thread): bool {
                return true;
            },
            $page,
            $pageSize,
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
        $tid = Database::insert(self::TABLE, $data);
        self::deleteApcuCache(self::homeNoticeCacheKey());
        return $tid;
    }

    public static function restore(array $data): int {
        unset(self::$memoryCache[(int)($data['tid'] ?? 0)]);
        self::deleteApcuCache(self::homeNoticeCacheKey());
        return Database::insert(self::TABLE, $data);
    }

    public static function update(int $tid, array $data): int {
        unset(self::$memoryCache[$tid]);
        if (empty($data)) {
            return 0;
        }
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

    public static function decrementReplyNum(int $tid, int $amount = 1): void {
        if ($tid <= 0 || $amount <= 0) {
            return;
        }
        unset(self::$memoryCache[$tid]);
        Database::query(
            "UPDATE " . self::TABLE . " SET reply_num = CASE WHEN reply_num > :amount_check THEN reply_num - :amount_dec ELSE 0 END WHERE tid = :tid",
            ['amount_check' => $amount, 'amount_dec' => $amount, 'tid' => $tid]
        );
        self::refreshLastReply($tid);
    }

    public static function rebuildReplyStats(int $tid): void {
        unset(self::$memoryCache[$tid]);
        $lastPost = PostModel::getLastPostByTid($tid);
        $thread = self::get($tid);
        $replyNum = Database::count(PostModel::TABLE, 'tid = :tid AND is_thread = 0', ['tid' => $tid]);
        Database::update(self::TABLE, [
            'reply_num' => $replyNum,
            'reply_uid' => (int)($lastPost['uid'] ?? 0),
            'reply_time' => (int)($lastPost['dateline'] ?? ($thread['dateline'] ?? time())),
        ], self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
        unset(self::$memoryCache[$tid]);
    }

    public static function refreshLastReply(int $tid): void {
        if ($tid <= 0) {
            return;
        }
        unset(self::$memoryCache[$tid]);
        $thread = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
        if (!$thread) {
            return;
        }
        $lastPost = PostModel::getLastPostByTid($tid);
        Database::update(self::TABLE, [
            'reply_uid' => (int)($lastPost['uid'] ?? 0),
            'reply_time' => (int)($lastPost['dateline'] ?? $thread['dateline']),
        ], self::PRIMARY_KEY . " = :tid", ['tid' => $tid]);
    }

    public static function getLatestTidByFid(int $fid): int {
        $row = Database::fetch(
            "SELECT * FROM " . self::TABLE . " WHERE fid = :fid ORDER BY tid DESC LIMIT 1",
            ['fid' => $fid]
        );
        return (int)($row['tid'] ?? 0);
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

    public static function search(int $page = 1, string $order = 'tid', int $fid = 0, int $uid = 0, string $keyword = '', int $pageSize = self::PAGE_SIZE): array {
        [$sql, $params] = self::buildIndexedListQuery($fid, $uid, $keyword);
        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            static function (array $thread) use ($fid, $uid): bool {
                return self::matchesThreadFilters($thread, $fid, $uid, false);
            },
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function searchCount(int $fid = 0, int $uid = 0, string $keyword = ''): int {
        if ($keyword === '') {
            if ($fid > 0 && $uid <= 0) {
                return self::getThreadCount($fid);
            }
            if ($uid > 0 && $fid <= 0) {
                return self::getUserThreadCount($uid);
            }
            if ($fid <= 0 && $uid <= 0) {
                return self::sumForumThreadCounts();
            }
        }

        [$sql, $params] = self::buildIndexedListQuery($fid, $uid, $keyword);
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
                return true;
            },
            $limit,
            self::FILTER_BATCH_SIZE
        );

        return ViewCounter::applyPendingToThreads($threads);
    }

    public static function getHomeThreadsWithFilter(int $page = 1, string $order = 'tid', string $keyword = '', int $pageSize = self::PAGE_SIZE, array $includeFids = []): array {
        $includeMap = self::buildFidMap($includeFids);
        [$sql, $params] = self::buildListQuery([], [], $keyword);
        $filter = static function (array $thread) use ($includeMap): bool {
            return empty($includeMap) || isset($includeMap[(int)$thread['fid']]);
        };

        $threads = Database::fetchFilteredPage(
            $sql,
            $params,
            $filter,
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function getHomeThreadCount(string $keyword = '', array $includeFids = []): int {
        $includeMap = self::buildFidMap($includeFids);
        if ($keyword === '') {
            if (empty($includeMap)) {
                return self::sumForumThreadCounts();
            }
            return self::sumForumThreadCounts(array_keys($includeMap));
        }

        [$sql, $params] = self::buildListQuery([], [], $keyword);
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread) use ($includeMap): bool {
                return empty($includeMap) || isset($includeMap[(int)$thread['fid']]);
            }
        );
    }

    public static function getGlobalHomeThreadCount(string $keyword = ''): int {
        if ($keyword === '') {
            return self::sumForumThreadCounts();
        }

        [$sql, $params] = self::buildListQuery([], [], $keyword);
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread): bool {
                return true;
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
                return empty($includeMap) || isset($includeMap[(int)$thread['fid']]);
            },
            $page,
            self::PAGE_SIZE,
            self::FILTER_BATCH_SIZE
        );
        
        return self::sortThreads(ViewCounter::applyPendingToThreads($threads), $order);
    }

    public static function getCollapsedThreadCount(string $keyword = '', array $includeFids = []): int {
        $includeMap = array_flip(array_map('intval', $includeFids));
        if ($keyword === '') {
            if (empty($includeMap)) {
                return self::sumForumThreadCounts();
            }
            return self::sumForumThreadCounts(array_keys($includeMap));
        }

        [$sql, $params] = self::buildListQuery([], [], $keyword);
        return Database::countFiltered(
            $sql,
            $params,
            static function (array $thread) use ($includeMap): bool {
                return empty($includeMap) || isset($includeMap[(int)$thread['fid']]);
            }
        );
    }

    public static function getPendingApproveCount(): int {
        return (int)(AuditModel::getPendingStats()['pending_threads'] ?? 0);
    }

    public static function getPendingThreadsByTids(array $tids): array {
        $tids = array_values(array_filter(array_unique(array_map('intval', $tids))));
        if (empty($tids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tids), '?'));
        $threads = Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE tid IN ($placeholders)",
            $tids
        );

        return array_column(ViewCounter::applyPendingToThreads($threads), null, 'tid');
    }

    public static function getThreadsByTids(array $tids): array {
        $tids = array_values(array_filter(array_unique(array_map('intval', $tids))));
        if (empty($tids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tids), '?'));
        $sql = "SELECT * FROM " . self::TABLE . " WHERE tid IN ($placeholders)";
        $threads = Database::fetchAll($sql, $tids);

        return array_column(ViewCounter::applyPendingToThreads($threads), null, 'tid');
    }

    public static function getHotThreadsByFid(int $fid, int $limit = 5, int $excludeTid = 0): array {
        $cacheKey = self::hotThreadsCacheKey($fid, $limit, $excludeTid);
        $cachedThreads = self::getApcuCache($cacheKey);
        if ($cachedThreads !== null) {
            return $cachedThreads;
        }

        $threads = Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE fid = :fid ORDER BY tid DESC LIMIT :limit",
            ['fid' => $fid, 'limit' => max($limit, 20)]
        );

        $threads = array_values(array_filter($threads, static function (array $thread) use ($excludeTid): bool {
            return (int)$thread['tid'] !== $excludeTid;
        }));

        $threads = array_slice(self::sortThreads(ViewCounter::applyPendingToThreads($threads), 'reply_num'), 0, $limit);
        self::setApcuCache($cacheKey, $threads, self::HOT_THREADS_CACHE_TTL);

        return $threads;
    }

    public static function getHomeNoticeThreads(int $noticeFid, int $limit = 5): array {
        if ($noticeFid <= 0 || $limit <= 0) {
            return [];
        }

        $cacheKey = self::homeNoticeCacheKey();
        $cachedThreads = self::getApcuCache($cacheKey);
        if ($cachedThreads !== null) {
            return $cachedThreads;
        }

        $threads = array_slice(self::getThreads($noticeFid, 1, 'dateline', ''), 0, $limit);
        self::setApcuCache($cacheKey, $threads, 0);

        return $threads;
    }

    public static function clearHomeNoticeCache(): void {
        self::deleteApcuCache(self::homeNoticeCacheKey());
    }

    private static function homeNoticeCacheKey(): string {
        return 'xforum:' . md5(ROOT_PATH) . ':' . self::HOME_NOTICE_CACHE_KEY;
    }

    private static function hotThreadsCacheKey(int $fid, int $limit, int $excludeTid): string {
        return 'xforum:' . md5(ROOT_PATH) . ':hot_threads:' . $fid . ':' . $limit . ':' . $excludeTid;
    }

    private static function getApcuCache(string $key): ?array {
        if (!self::isApcuAvailable()) {
            return null;
        }

        $success = false;
        $data = apcu_fetch($key, $success);
        return $success && is_array($data) ? $data : null;
    }

    private static function setApcuCache(string $key, array $data, int $ttl): void {
        if (!self::isApcuAvailable()) {
            return;
        }

        apcu_store($key, $data, $ttl);
    }

    private static function deleteApcuCache(string $key): void {
        if (!self::isApcuAvailable() || !function_exists('apcu_delete')) {
            return;
        }

        apcu_delete($key);
    }

    private static function isApcuAvailable(): bool {
        if (!function_exists('apcu_fetch') || !function_exists('apcu_store')) {
            return false;
        }

        if (function_exists('apcu_enabled')) {
            return apcu_enabled();
        }

        return filter_var((string)ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    private static function matchesThreadFilters(array $thread, int $fid, int $uid, bool $approvedOnly): bool {
        if ($fid > 0 && (int)$thread['fid'] !== $fid) {
            return false;
        }
        if ($uid > 0 && (int)$thread['uid'] !== $uid) {
            return false;
        }
        return true;
    }

    private static function buildIndexedListQuery(int $fid, int $uid, string $keyword = ''): array {
        if ($fid > 0) {
            return self::buildListQuery(['fid = :fid'], ['fid' => $fid], $keyword);
        }

        if ($uid > 0) {
            return self::buildListQuery(['uid = :uid'], ['uid' => $uid], $keyword);
        }

        return self::buildListQuery([], [], $keyword);
    }

    private static function buildFidMap(array $fids): array {
        $fids = array_values(array_filter(array_unique(array_map('intval', $fids))));
        return empty($fids) ? [] : array_fill_keys($fids, true);
    }

    private static function sumForumThreadCounts(array $fids = []): int {
        $forums = empty($fids) ? ForumModel::getForumsFlat() : ForumModel::getForumsByIds($fids);
        $total = 0;
        foreach ($forums as $forum) {
            $total += (int)($forum['thread_num'] ?? 0);
        }
        return $total;
    }

    private static function buildListQuery(array $where, array $params, string $keyword = ''): array {
        $keyword = trim($keyword);
        if ($keyword !== '') {
            $where[] = "subject LIKE :keyword ESCAPE '~'";
            $params['keyword'] = '%' . self::escapeLikeKeyword($keyword) . '%';
        }

        $whereSql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
        return [
            "SELECT * FROM " . self::TABLE . $whereSql . " ORDER BY tid DESC LIMIT :limit OFFSET :offset",
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

        $allowedOrders = ['reply_time', 'dateline', 'reply_num', 'view_num'];
        if (!in_array($order, $allowedOrders, true)) {
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
