<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;

class RateModel {
    const TABLE = 'next_rate';

    public static function getRatedPids(int $uid, array $pids): array {
        $pids = array_values(array_filter(array_unique(array_map('intval', $pids))));
        if ($uid <= 0 || empty($pids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $rows = Database::fetchAll(
            "SELECT pid FROM " . self::TABLE . " WHERE uid = ? AND pid IN ($placeholders)",
            array_merge([$uid], $pids)
        );

        $ratedPids = [];
        foreach ($rows as $row) {
            $ratedPids[(int)$row['pid']] = true;
        }
        return $ratedPids;
    }

    public static function isRated(int $uid, int $pid): bool {
        if ($uid <= 0 || $pid <= 0) {
            return false;
        }

        $result = Database::fetch(
            "SELECT pid FROM " . self::TABLE . " WHERE uid = :uid AND pid = :pid",
            ['uid' => $uid, 'pid' => $pid]
        );
        return !empty($result);
    }

    public static function addRate(int $uid, int $pid): bool {
        if ($uid <= 0 || $pid <= 0 || self::isRated($uid, $pid)) {
            return false;
        }

        Database::insert(self::TABLE, [
            'uid' => $uid,
            'pid' => $pid,
            'dateline' => time(),
        ]);
        PostModel::incrementRateNum($pid);
        return true;
    }

    public static function removeRate(int $uid, int $pid): bool {
        if ($uid <= 0 || $pid <= 0) {
            return false;
        }

        $deleted = Database::delete(
            self::TABLE,
            'uid = :uid AND pid = :pid',
            ['uid' => $uid, 'pid' => $pid]
        );
        if ($deleted > 0) {
            PostModel::decrementRateNum($pid);
            return true;
        }
        return false;
    }
}
?>
