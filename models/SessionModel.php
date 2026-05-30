<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class SessionModel {
    const TABLE = 'next_session';
    const ONLINE_TIMEOUT = 1800;

    private static ?int $onlineCountCache = null;
    private static array $onlineUsersCache = [];
    private static int $cacheTime = 0;
    const CACHE_TTL = 60;

    public static function updateOnline(int $uid = 0, int $gid = 0, int $invisible = 0, int $fid = 0, int $tid = 0): void {
        $sessionKey = session_id() ?: md5(uniqid($_SERVER['REMOTE_ADDR'], true));
        
        $existing = Database::fetch(
            "SELECT * FROM " . self::TABLE . " WHERE session_key = :session_key",
            ['session_key' => $sessionKey]
        );

        if ($existing) {
            $newDateline = time();
            $newIp = $_SERVER['REMOTE_ADDR'];
            
            $needsUpdate = false;
            $updateData = [];
            
            if ($existing['uid'] !== $uid) {
                $updateData['uid'] = $uid;
                $needsUpdate = true;
            }
            
            if ($existing['gid'] !== $gid) {
                $updateData['gid'] = $gid;
                $needsUpdate = true;
            }
            
            if ($existing['invisible'] !== $invisible) {
                $updateData['invisible'] = $invisible;
                $needsUpdate = true;
            }
            
            if ($existing['ip'] !== $newIp) {
                $updateData['ip'] = $newIp;
                $needsUpdate = true;
            }
            
            if (abs($existing['dateline'] - $newDateline) >= 60) {
                $updateData['dateline'] = $newDateline;
                $needsUpdate = true;
            }
            
            if ($fid > 0 || $tid > 0) {
                if ($existing['fid'] !== $fid) {
                    $updateData['fid'] = $fid;
                    $needsUpdate = true;
                }
                
                if ($existing['tid'] !== $tid) {
                    $updateData['tid'] = $tid;
                    $needsUpdate = true;
                }
            }
            
            if ($needsUpdate) {
                self::invalidateCache();
                $updateData['dateline'] = $newDateline;
                Database::update(self::TABLE, $updateData, 'id = :id', ['id' => $existing['id']]);
            }
        } else {
            self::invalidateCache();
            Database::insert(self::TABLE, [
                'session_key' => $sessionKey,
                'uid' => $uid,
                'gid' => $gid,
                'invisible' => $invisible,
                'fid' => $fid,
                'tid' => $tid,
                'dateline' => time(),
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
        }
    }

    public static function getOnlineCount(): int {
        if (self::$onlineCountCache !== null && (time() - self::$cacheTime) < self::CACHE_TTL) {
            return self::$onlineCountCache;
        }

        self::$onlineCountCache = count(self::getOnlineRows());
        self::$cacheTime = time();
        return self::$onlineCountCache;
    }

    public static function getOnlineMemberCount(): int {
        $timeout = time() - self::ONLINE_TIMEOUT;
        $rows = Database::fetchAll(
            "SELECT uid FROM " . self::TABLE . " WHERE dateline > :timeout ORDER BY dateline DESC",
            ['timeout' => $timeout]
        );

        $uids = [];
        foreach ($rows as $row) {
            $uid = (int)($row['uid'] ?? 0);
            if ($uid > 0) {
                $uids[$uid] = true;
            }
        }

        return count($uids);
    }

    public static function getOnlineUsers(): array {
        if (!empty(self::$onlineUsersCache) && (time() - self::$cacheTime) < self::CACHE_TTL) {
            return self::$onlineUsersCache;
        }

        self::$onlineUsersCache = self::getOnlineRows();
        self::$cacheTime = time();
        return self::$onlineUsersCache;
    }

    public static function cleanup(): void {
        self::invalidateCache();
        $timeout = time() - self::ONLINE_TIMEOUT;
        Database::delete(self::TABLE, 'dateline < :timeout', ['timeout' => $timeout]);
    }

    private static function invalidateCache(): void {
        self::$onlineUsersCache = [];
        self::$onlineCountCache = null;
        self::$cacheTime = 0;
    }

    private static function getOnlineRows(): array {
        $timeout = time() - self::ONLINE_TIMEOUT;
        $rows = Database::fetchAll(
            "SELECT uid, gid, fid, tid, dateline, ip FROM " . self::TABLE . " WHERE dateline > :timeout ORDER BY dateline DESC",
            ['timeout' => $timeout]
        );

        $result = [];
        $seenUids = [];
        foreach ($rows as $row) {
            $uid = (int)($row['uid'] ?? 0);
            if ($uid > 0) {
                if (isset($seenUids[$uid])) {
                    continue;
                }
                $seenUids[$uid] = true;
            }
            $result[] = $row;
        }
        return $result;
    }
}
?>
