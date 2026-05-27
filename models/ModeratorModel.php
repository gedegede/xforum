<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class ModeratorModel {
    const TABLE = 'next_moderator';

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return $cache;
        }

        $data = Database::fetchAll("SELECT * FROM " . self::TABLE);
        CacheHelper::setCache(self::TABLE, $data);
        return $data;
    }

    public static function getByUid(int $uid): array {
        $all = self::getAll();
        $result = [];
        foreach ($all as $row) {
            if ($row['uid'] == $uid) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function getByFid(int $fid): array {
        $all = self::getAll();
        $result = [];
        foreach ($all as $row) {
            if ($row['fid'] == $fid) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function create(array $data): int {
        $id = Database::insert(self::TABLE, $data);
        CacheHelper::deleteCache(self::TABLE);
        return $id;
    }

    public static function update(int $uid, int $fid, array $data): int {
        $result = Database::update(self::TABLE, $data, "uid = :uid AND fid = :fid", ['uid' => $uid, 'fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function delete(int $uid, int $fid): int {
        $result = Database::delete(self::TABLE, "uid = :uid AND fid = :fid", ['uid' => $uid, 'fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function deleteByFid(int $fid): int {
        $result = Database::delete(self::TABLE, 'fid = :fid', ['fid' => $fid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function isModerator(int $uid, int $fid): bool {
        $all = self::getAll();
        foreach ($all as $row) {
            if ($row['uid'] == $uid && $row['fid'] == $fid) {
                return true;
            }
        }
        return false;
    }
}
?>
