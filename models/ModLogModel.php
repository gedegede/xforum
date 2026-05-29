<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class ModLogModel {
    const TABLE = 'next_mod_log';
    const PRIMARY_KEY = 'did';

    public static function addLog(int $uid, string $action, string $message, int $tid = 0, int $pid = 0, int $authorid = 0, string $archiveData = ''): void {
        Database::insert(self::TABLE, [
            'tid' => $tid,
            'pid' => $pid,
            'uid' => $uid,
            'authorid' => $authorid,
            'action' => $action,
            'message' => $message,
            'archive_data' => $archiveData,
            'dateline' => time(),
        ]);
    }

    public static function getLogs(int $page = 1, int $tid = 0, string $message = '', int $uid = 0, string $action = '', int $authorid = 0): array {
        $offset = ($page - 1) * 20;
        [$where, $params] = self::buildFilter($tid, $message, $uid, $action, $authorid);
        $params['offset'] = $offset;
        return Database::fetchAll("SELECT * FROM " . self::TABLE . " $where ORDER BY did DESC LIMIT 20 OFFSET :offset", $params);
    }

    public static function get(int $did): ?array {
        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE did = :did", ['did' => $did]);
    }

    public static function getCount(int $tid = 0, string $message = '', int $uid = 0, string $action = '', int $authorid = 0): int {
        [$where, $params] = self::buildFilter($tid, $message, $uid, $action, $authorid);
        $result = Database::fetch("SELECT COUNT(*) as count FROM " . self::TABLE . " $where", $params);
        return (int)($result['count'] ?? 0);
    }

    private static function buildFilter(int $tid, string $message, int $uid, string $action, int $authorid): array {
        $where = [];
        $params = [];
        if ($action !== '') {
            $where[] = 'action = :action';
            $params['action'] = $action;
        }
        if ($authorid > 0) {
            $where[] = 'authorid = :authorid';
            $params['authorid'] = $authorid;
        }
        if ($uid > 0) {
            $where[] = 'uid = :uid';
            $params['uid'] = $uid;
        }
        if ($tid > 0) {
            $where[] = 'tid = :tid';
            $params['tid'] = $tid;
        }
        if ($message !== '') {
            $where[] = 'message LIKE :message';
            $params['message'] = '%' . $message . '%';
        }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }
}
?>
