<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class AuditModel {
    const TABLE = 'next_audit';
    const ARCHIVE_TABLE = 'next_audit_archive';
    private const STATS_CACHE_KEY = 'audit_pending_stats';

    public static function create(string $type, int $tid, int $pid = 0, array $jsonData = [], int $fid = 0, int $uid = 0): int {
        $did = Database::insert(self::TABLE, [
            'fid' => $fid,
            'uid' => $uid,
            'tid' => $tid,
            'pid' => $pid,
            'type' => $type,
            'dateline' => time(),
            'json_data' => json_encode($jsonData, JSON_UNESCAPED_UNICODE),
        ]);
        self::updatePendingStats($type, 1);
        return $did;
    }

    public static function get(int $did): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE did = :did", ['did' => $did]);
    }

    public static function getArchive(int $did): ?array {
        return Database::fetch("SELECT * FROM " . self::ARCHIVE_TABLE . " WHERE did = :did", ['did' => $did]);
    }

    public static function getList(string $filter, int $limit = 50): array {
        $params = ['limit' => $limit];
        if ($filter === 'thread' || $filter === 'post' || $filter === 'report') {
            $params['type'] = $filter;
            return Database::fetchAll(
                "SELECT *, 0 AS audit_status FROM " . self::TABLE . " WHERE type = :type ORDER BY did DESC LIMIT :limit",
                $params
            );
        } elseif ($filter === 'done') {
            return Database::fetchAll(
                "SELECT * FROM " . self::ARCHIVE_TABLE . " WHERE status = 1 ORDER BY did DESC LIMIT :limit",
                $params
            );
        } elseif ($filter === 'rejected') {
            return Database::fetchAll(
                "SELECT * FROM " . self::ARCHIVE_TABLE . " WHERE status = -1 ORDER BY did DESC LIMIT :limit",
                $params
            );
        }

        return Database::fetchAll(
            "SELECT *, 0 AS audit_status FROM " . self::TABLE . " ORDER BY did DESC LIMIT :limit",
            $params
        );
    }

    public static function countPending(): int {
        return Database::count(self::TABLE);
    }

    public static function hasPending(string $type, int $tid, int $pid = 0): bool {
        return Database::count(
            self::TABLE,
            'tid = :tid AND pid = :pid AND type = :type',
            ['type' => $type, 'tid' => $tid, 'pid' => $pid]
        ) > 0;
    }

    public static function hasPendingByUid(string $type, int $uid): bool {
        if ($uid <= 0) {
            return false;
        }

        return Database::count(self::TABLE, 'uid = :uid AND type = :type', ['type' => $type, 'uid' => $uid]) > 0;
    }

    public static function finishPendingByTarget(string $type, int $tid, int $pid, int $status, int $uid): int {
        $audits = Database::fetchAll(
            "SELECT did FROM " . self::TABLE . " WHERE tid = :tid AND pid = :pid AND type = :type",
            ['tid' => $tid, 'pid' => $pid, 'type' => $type]
        );
        foreach ($audits as $audit) {
            self::finish((int)$audit['did'], $status, $uid);
        }
        return count($audits);
    }

    public static function finishPendingByThread(int $tid, int $status, int $uid): array {
        $audits = Database::fetchAll(
            "SELECT did, type FROM " . self::TABLE . " WHERE tid = :tid",
            ['tid' => $tid]
        );
        $counts = ['thread' => 0, 'post' => 0, 'report' => 0];
        foreach ($audits as $audit) {
            self::finish((int)$audit['did'], $status, $uid);
            $type = (string)($audit['type'] ?? '');
            if (isset($counts[$type])) {
                $counts[$type]++;
            }
        }
        return $counts;
    }

    public static function getPendingStats(): array {
        $stats = CacheHelper::getCache(self::STATS_CACHE_KEY);
        if ($stats !== null) {
            return [
                'pending_threads' => (int)($stats['pending_threads'] ?? 0),
                'pending_posts' => (int)($stats['pending_posts'] ?? 0),
                'pending_reports' => (int)($stats['pending_reports'] ?? 0),
            ];
        }

        $rows = Database::fetchAll(
            "SELECT type, COUNT(*) AS total FROM " . self::TABLE . " GROUP BY type"
        );
        $stats = ['pending_threads' => 0, 'pending_posts' => 0, 'pending_reports' => 0];
        foreach ($rows as $row) {
            if ($row['type'] === 'thread') {
                $stats['pending_threads'] = (int)$row['total'];
            } elseif ($row['type'] === 'post') {
                $stats['pending_posts'] = (int)$row['total'];
            } elseif ($row['type'] === 'report') {
                $stats['pending_reports'] = (int)$row['total'];
            }
        }
        CacheHelper::setCache(self::STATS_CACHE_KEY, $stats);
        return $stats;
    }

    public static function updatePendingStats(string $type, int $delta): void {
        $stats = self::getPendingStats();
        $key = match ($type) {
            'thread' => 'pending_threads',
            'post' => 'pending_posts',
            'report' => 'pending_reports',
            default => '',
        };
        if ($key === '') {
            return;
        }
        $stats[$key] = max(0, (int)$stats[$key] + $delta);
        CacheHelper::setCache(self::STATS_CACHE_KEY, $stats);
    }

    public static function finish(int $did, int $status, int $uid): void {
        $audit = self::get($did);
        if (!$audit) {
            return;
        }

        $jsonData = json_decode((string)($audit['json_data'] ?? '{}'), true);
        if (!is_array($jsonData)) {
            $jsonData = [];
        }
        $jsonData['audit_uid'] = $uid;
        $jsonData['audit_status'] = $status;
        $jsonData['audit_time'] = time();

        Database::insert(self::ARCHIVE_TABLE, [
            'did' => (int)$audit['did'],
            'fid' => (int)$audit['fid'],
            'uid' => (int)$audit['uid'],
            'tid' => (int)$audit['tid'],
            'pid' => (int)$audit['pid'],
            'status' => $status,
            'type' => (string)$audit['type'],
            'dateline' => (int)$audit['dateline'],
            'json_data' => json_encode($jsonData, JSON_UNESCAPED_UNICODE),
        ]);
        Database::delete(self::TABLE, 'did = :did', ['did' => $did]);
        self::updatePendingStats((string)$audit['type'], -1);
    }
}
