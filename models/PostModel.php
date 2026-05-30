<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Models\ThreadModel;

class PostModel {
    const TABLE = 'next_post';
    const PRIMARY_KEY = 'pid';
    private const PAGE_SIZE = 20;
    private const FILTER_BATCH_SIZE = 100;

    public static function getPosts(int $tid, int $page = 1, bool $includePending = false, int $pageSize = self::PAGE_SIZE): array {
        return Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE tid = :tid ORDER BY pid ASC LIMIT :limit OFFSET :offset",
            ['tid' => $tid],
            static function (array $post): bool {
                return true;
            },
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
    }

    public static function getPendingApproveCount(): int {
        return (int)(AuditModel::getPendingStats()['pending_posts'] ?? 0);
    }

    public static function get(int $pid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :pid", ['pid' => $pid]);
    }

    public static function getThreadPost(int $tid): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE tid = :tid AND is_thread = 1 ORDER BY pid ASC LIMIT 1", ['tid' => $tid]);
    }

    public static function getPostsByPids(array $pids): array {
        $pids = array_values(array_filter(array_unique(array_map('intval', $pids))));
        if (empty($pids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        return array_column(Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE pid IN ($placeholders)",
            $pids
        ), null, 'pid');
    }

    public static function getPostCount(int $tid, bool $includePending = false): int {
        $thread = ThreadModel::get($tid);
        if (!$thread) {
            return 0;
        }
        return (int)($thread['reply_num'] ?? 0) + 1;
    }

    public static function getUserPosts(int $uid, int $page = 1, int $pageSize = self::PAGE_SIZE): array {
        $threadCache = [];
        return Database::fetchFilteredPage(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid ORDER BY pid DESC LIMIT :limit OFFSET :offset",
            ['uid' => $uid],
            static function (array $post) use (&$threadCache): bool {
                if ((int)($post['is_thread'] ?? 0) !== 0) {
                    return false;
                }
                $tid = (int)($post['tid'] ?? 0);
                if (!array_key_exists($tid, $threadCache)) {
                    $threadCache[$tid] = ThreadModel::get($tid) !== null;
                }
                return $threadCache[$tid];
            },
            $page,
            $pageSize,
            self::FILTER_BATCH_SIZE
        );
    }

    public static function getUserReplyRows(int $uid): array {
        if ($uid <= 0) {
            return [];
        }
        $threadCache = [];
        $posts = Database::fetchAll(
            "SELECT * FROM " . self::TABLE . " WHERE uid = :uid AND is_thread = 0 ORDER BY pid DESC",
            ['uid' => $uid]
        );
        return array_values(array_filter($posts, static function (array $post) use (&$threadCache): bool {
            $tid = (int)($post['tid'] ?? 0);
            if (!array_key_exists($tid, $threadCache)) {
                $threadCache[$tid] = ThreadModel::get($tid) !== null;
            }
            return $threadCache[$tid];
        }));
    }

    public static function getUserPostCount(int $uid): int {
        $member = MemberModel::get($uid);
        return (int)($member['reply_num'] ?? 0);
    }

    public static function create(array $data): int {
        $data['dateline'] = time();
        $data['ip'] = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $data['credit_log'] = $data['credit_log'] ?? '[]';
        return Database::insert(self::TABLE, $data);
    }

    public static function restore(array $data): int {
        $data['credit_log'] = $data['credit_log'] ?? '[]';
        return Database::insert(self::TABLE, $data);
    }

    public static function deleteByTid(int $tid): void {
        Database::query("DELETE FROM " . self::TABLE . " WHERE tid = :tid", ['tid' => $tid]);
    }

    public static function getPostsByTid(int $tid): array {
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " WHERE tid = :tid ORDER BY pid ASC", ['tid' => $tid]);
    }

    public static function updateFidByTid(int $tid, int $fid): int {
        return Database::update(self::TABLE, ['fid' => $fid], 'tid = :tid', ['tid' => $tid]);
    }

    public static function getLastPostByTid(int $tid): ?array {
        $posts = Database::fetchFilteredLimit(
            "SELECT * FROM " . self::TABLE . " WHERE tid = :tid ORDER BY pid DESC LIMIT :limit OFFSET :offset",
            ['tid' => $tid],
            static function (array $post): bool {
                return (int)($post['is_thread'] ?? 0) === 0;
            },
            1,
            self::FILTER_BATCH_SIZE
        );

        return $posts[0] ?? null;
    }

    public static function getPostFloor(int $pid): int {
        return Database::count(
            self::TABLE,
            "tid = (SELECT tid FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :target_pid) AND " . self::PRIMARY_KEY . " <= :pid",
            ['target_pid' => $pid, 'pid' => $pid]
        );
    }

    public static function getPostPage(int $pid, int $pageSize = self::PAGE_SIZE): int {
        $floor = self::getPostFloor($pid);
        if ($floor <= 0) {
            return 0;
        }

        return (int)ceil($floor / max(1, $pageSize));
    }

    public static function update(int $pid, array $data): int {
        if (empty($data)) {
            return 0;
        }
        $data['pid'] = $pid;
        $data['edited'] = time();
        return Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :pid");
    }

    public static function incrementRateNum(int $pid): void {
        if ($pid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET rate_num = rate_num + 1 WHERE pid = :pid", ['pid' => $pid]);
    }

    public static function decrementRateNum(int $pid): void {
        if ($pid <= 0) return;
        Database::query("UPDATE " . self::TABLE . " SET rate_num = CASE WHEN rate_num > 0 THEN rate_num - 1 ELSE 0 END WHERE pid = :pid", ['pid' => $pid]);
    }

    public static function addCreditLog(int $pid, array $log): void {
        if ($pid <= 0) {
            return;
        }

        $post = Database::fetch(
            "SELECT credit_log FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :pid FOR UPDATE",
            ['pid' => $pid]
        );
        if (!$post) {
            return;
        }

        $logs = json_decode((string)($post['credit_log'] ?? '[]'), true);
        if (!is_array($logs)) {
            $logs = [];
        }
        $logs[] = $log;

        Database::query(
            "UPDATE " . self::TABLE . " SET credit_log = :credit_log WHERE pid = :pid",
            ['credit_log' => json_encode($logs, JSON_UNESCAPED_UNICODE), 'pid' => $pid]
        );
    }

    public static function delete(int $pid): int {
        return Database::delete(self::TABLE, self::PRIMARY_KEY . " = :pid", ['pid' => $pid]);
    }
}
?>
