<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class ForumModel {
    const TABLE = 'next_forum';
    const PRIMARY_KEY = 'fid';
    public const GROUP_PERMISSION_KEYS = ['view', 'thread', 'reply'];

    public static function getForums(?int $upFid = null): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            $forums = array_values($cache);
        } else {
            $forums = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY fid ASC");
            self::sortForums($forums);
            $indexed = [];
            foreach ($forums as $forum) {
                $indexed[$forum['fid']] = $forum;
            }
            CacheHelper::setCache(self::TABLE, $indexed);
        }

        if (empty($forums)) {
            return [];
        }

        $parentFids = array_unique(array_column($forums, 'up_fid'));
        $parentFids = array_values(array_filter($parentFids, function($fid) { return $fid !== null && $fid !== ''; }));
        $parentNames = [];
        if (!empty($parentFids)) {
            $parentNames = self::getForumNamesByFids($parentFids, $forums);
        }

        foreach ($forums as &$forum) {
            $forum = self::parseJsonDataItem($forum);
            $forum['parent_name'] = $parentNames[$forum['up_fid']] ?? '';
        }

        if ($upFid === null) {
            $tree = self::buildTree($forums);
            self::addDepth($tree, 0);
            return $tree;
        }

        return array_filter($forums, function($forum) use ($upFid) {
            return $forum['up_fid'] == $upFid;
        });
    }

    private static function sortForums(array &$forums): void {
        usort($forums, static function (array $a, array $b): int {
            $upCompare = (int)$a['up_fid'] <=> (int)$b['up_fid'];
            if ($upCompare !== 0) {
                return $upCompare;
            }

            $sortCompare = (int)$a['sort_order'] <=> (int)$b['sort_order'];
            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return (int)$a['fid'] <=> (int)$b['fid'];
        });
    }

    private static function getForumNamesByFids(array $fids, array $forums): array {
        if (empty($fids)) {
            return [];
        }

        $result = [];
        foreach ($forums as $forum) {
            if (in_array($forum['fid'], $fids)) {
                $result[$forum['fid']] = $forum['name'];
            }
        }
        return $result;
    }

    private static function buildTree(array $forums): array {
        $tree = [];
        $map = [];

        foreach ($forums as $forum) {
            $map[$forum['fid']] = $forum;
            $map[$forum['fid']]['children'] = [];
        }

        foreach ($forums as $forum) {
            $fid = $forum['fid'];
            $upFid = $forum['up_fid'];

            if ($upFid && isset($map[$upFid])) {
                $map[$upFid]['children'][] = &$map[$fid];
            } else {
                $tree[] = &$map[$fid];
            }
        }

        return $tree;
    }

    private static function addDepth(array &$forums, int $depth): void {
        if (!is_array($forums)) {
            return;
        }
        foreach ($forums as &$forum) {
            $forum['depth'] = $depth;
            if (!empty($forum['children'])) {
                self::addDepth($forum['children'], $depth + 1);
            }
        }
    }

    private static function flattenTree(array $tree, array &$result = []): array {
        foreach ($tree as $forum) {
            $result[] = $forum;
            if (!empty($forum['children'])) {
                self::flattenTree($forum['children'], $result);
            }
        }
        return $result;
    }

    public static function getForumsFlat(): array {
        $forums = self::getForums();
        return self::flattenTree($forums);
    }

    public static function getDescendantsFlat(int $fid): array {
        $forums = self::getForumsFlat();
        if (empty($forums)) {
            return [];
        }

        $childrenByParent = [];
        foreach ($forums as $forum) {
            $parentFid = (int)($forum['up_fid'] ?? 0);
            $childrenByParent[$parentFid][] = $forum;
        }

        $result = [];
        self::collectDescendants($fid, $childrenByParent, $result, 1);
        return $result;
    }

    private static function collectDescendants(int $fid, array $childrenByParent, array &$result, int $level): void {
        if (empty($childrenByParent[$fid])) {
            return;
        }

        foreach ($childrenByParent[$fid] as $forum) {
            $forum['relative_depth'] = $level;
            $result[] = $forum;
            self::collectDescendants((int)$forum['fid'], $childrenByParent, $result, $level + 1);
        }
    }

    public static function get(int $fid): ?array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return isset($cache[$fid]) ? self::parseJsonDataItem($cache[$fid]) : null;
        }

        $forum = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE fid = ?", [$fid]);
        return $forum ? self::parseJsonDataItem($forum) : null;
    }

    private static function parseJsonDataItem(array $forum): array {
        if (!empty($forum['json_data'])) {
            $json = json_decode($forum['json_data'], true);
            if (is_array($json)) {
                $forum = array_merge($forum, $json);
            }
        }
        return $forum;
    }

    public static function canGroup(int $fid, int $gid, string $action): bool {
        if (!in_array($action, self::GROUP_PERMISSION_KEYS, true)) {
            return false;
        }

        $forum = self::get($fid);
        if (!$forum) {
            return false;
        }

        $permissions = $forum['group_permissions'][$action] ?? [];
        if (empty($permissions)) {
            return true;
        }
        return $gid > 0 && in_array($gid, array_map('intval', (array)$permissions), true);
    }

    public static function getForumName(int $fid): string {
        $forum = self::get($fid);
        return $forum['name'] ?? '';
    }

    public static function create(array $data): int {
        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }
        if (!isset($data['json_data'])) {
            $data['json_data'] = '{}';
        }
        $id = Database::insert(self::TABLE, $data);
        CacheHelper::deleteCache(self::TABLE);
        return $id;
    }

    public static function update(int $fid, array $data): int {
        $result = Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function delete(int $fid): int {
        $result = Database::delete(self::TABLE, self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function rebuildStats(int $fid): void {
        if ($fid <= 0) {
            return;
        }

        $threadCount = Database::count(ThreadModel::TABLE, 'fid = :fid AND sort_order >= 0', ['fid' => $fid]);
        $replyRow = Database::fetch(
            'SELECT COALESCE(SUM(reply_num), 0) AS reply_num, COALESCE(MAX(tid), 0) AS last_tid FROM ' . ThreadModel::TABLE . ' WHERE fid = :fid AND sort_order >= 0',
            ['fid' => $fid]
        );
        $todayTime = strtotime(date('Y-m-d'));
        $todayThreads = Database::count(ThreadModel::TABLE, 'fid = :fid AND sort_order >= 0 AND dateline >= :today_time', ['fid' => $fid, 'today_time' => $todayTime]);
        $todayReplies = Database::count(PostModel::TABLE, 'fid = :fid AND is_thread = 0 AND sort_order >= 0 AND dateline >= :today_time', ['fid' => $fid, 'today_time' => $todayTime]);

        Database::update(self::TABLE, [
            'thread_num' => $threadCount,
            'reply_num' => (int)($replyRow['reply_num'] ?? 0),
            'last_tid' => (int)($replyRow['last_tid'] ?? 0),
            'today_num' => $todayThreads + $todayReplies,
            'today_time' => $todayTime,
        ], self::PRIMARY_KEY . ' = :fid', ['fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
    }

    public static function count(): int {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return count($cache);
        }
        return Database::count(self::TABLE);
    }

    public static function incrementTodayNum(int $fid): void {
        $forum = self::get($fid);
        if (!$forum) {
            return;
        }

        $today = strtotime(date('Y-m-d'));
        if ($forum['today_time'] == $today) {
            Database::update(self::TABLE, [
                'today_num' => $forum['today_num'] + 1,
            ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
            $forum['today_num'] = $forum['today_num'] + 1;
        } else {
            Database::update(self::TABLE, [
                'today_num' => 1,
                'today_time' => $today,
            ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
            $forum['today_num'] = 1;
            $forum['today_time'] = $today;
        }
        self::updateCache($forum);
    }

    public static function incrementThreadNum(int $fid, int $tid): void {
        $forum = self::get($fid);
        if (!$forum) {
            return;
        }
        Database::update(self::TABLE, [
            'thread_num' => $forum['thread_num'] + 1,
            'last_tid' => $tid,
        ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        $forum['thread_num'] = $forum['thread_num'] + 1;
        $forum['last_tid'] = $tid;
        self::updateCache($forum);
    }

    public static function incrementReplyNum(int $fid, int $tid): void {
        $forum = self::get($fid);
        if (!$forum) {
            return;
        }
        Database::update(self::TABLE, [
            'reply_num' => $forum['reply_num'] + 1,
            'last_tid' => $tid,
        ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        $forum['reply_num'] = $forum['reply_num'] + 1;
        $forum['last_tid'] = $tid;
        self::updateCache($forum);
    }

    public static function decrementThreadNum(int $fid): void {
        $forum = self::get($fid);
        if (!$forum) {
            return;
        }
        Database::update(self::TABLE, [
            'thread_num' => max(0, $forum['thread_num'] - 1),
        ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        $forum['thread_num'] = max(0, $forum['thread_num'] - 1);
        self::updateCache($forum);
    }

    public static function decrementReplyNum(int $fid): void {
        $forum = self::get($fid);
        if (!$forum) {
            return;
        }
        Database::update(self::TABLE, [
            'reply_num' => max(0, $forum['reply_num'] - 1),
        ], self::PRIMARY_KEY . " = :fid", ['fid' => $fid]);
        $forum['reply_num'] = max(0, $forum['reply_num'] - 1);
        self::updateCache($forum);
    }

    private static function updateCache(array $forum): void {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            $cache[$forum['fid']] = $forum;
            CacheHelper::setCache(self::TABLE, $cache);
        }
    }

    public static function getHotForums(int $limit = 10): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            $forums = array_values($cache);
            usort($forums, static function (array $a, array $b): int {
                return (int)($b['today_num'] ?? 0) <=> (int)($a['today_num'] ?? 0);
            });

            return array_slice($forums, 0, $limit);
        }

        $forums = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY fid ASC");
        usort($forums, static function (array $a, array $b): int {
            $todayCompare = (int)($b['today_num'] ?? 0) <=> (int)($a['today_num'] ?? 0);
            if ($todayCompare !== 0) {
                return $todayCompare;
            }

            return (int)$a['fid'] <=> (int)$b['fid'];
        });

        return array_slice($forums, 0, $limit);
    }

    public static function getForumsByIds(array $fids): array {
        if (empty($fids)) {
            return [];
        }

        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            $result = [];
            foreach ($fids as $fid) {
                if (isset($cache[$fid])) {
                    $result[$fid] = $cache[$fid];
                }
            }
            return $result;
        }

        $placeholders = implode(',', array_fill(0, count($fids), '?'));
        $rows = Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE fid IN ($placeholders)", $fids);
        // 返回以 fid 为 key 的关联数组
        return array_column($rows, null, 'fid');
    }
}
?>
