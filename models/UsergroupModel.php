<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class UsergroupModel {
    const TABLE = 'next_usergroup';
    const PRIMARY_KEY = 'gid';

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return self::parseJsonData($cache);
        }

        $groups = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
        $indexed = [];
        foreach ($groups as $group) {
            $indexed[$group['gid']] = $group;
        }
        CacheHelper::setCache(self::TABLE, $indexed);
        return self::parseJsonData($indexed);
    }

    public static function get(int $gid): ?array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return isset($cache[$gid]) ? self::parseJsonDataItem($cache[$gid]) : null;
        }

        $group = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
        if ($group) {
            $group = self::parseJsonDataItem($group);
        }
        return $group;
    }

    private static function parseJsonData(array $groups): array {
        foreach ($groups as &$group) {
            $group = self::parseJsonDataItem($group);
        }
        return $groups;
    }

    private static function parseJsonDataItem(array $group): array {
        if (!empty($group['json_data'])) {
            $json = json_decode($group['json_data'], true);
            if (is_array($json)) {
                $group = array_merge($group, $json);
            }
        }
        return $group;
    }

    public static function canManage(int $gid): bool {
        $group = self::get($gid);
        return $group ? (bool)($group['can_manage'] ?? false) : false;
    }

    public static function threadNeedApprove(int $gid): bool {
        $group = self::get($gid);
        return $group ? (bool)($group['thread_need_approve'] ?? false) : false;
    }

    public static function postNeedApprove(int $gid): bool {
        $group = self::get($gid);
        return $group ? (bool)($group['post_need_approve'] ?? false) : false;
    }

    public static function getDefaultGroup(): ?array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            foreach ($cache as $group) {
                if ($group['group_type'] == 'member') {
                    return self::parseJsonDataItem($group);
                }
            }
            return null;
        }

        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE group_type = :group_type ORDER BY gid ASC LIMIT 1", ['group_type' => 'member']);
    }

    public static function create(array $data): int {
        $id = Database::insert(self::TABLE, $data);
        CacheHelper::deleteCache(self::TABLE);
        return $id;
    }

    public static function update(int $gid, array $data): int {
        $data['gid'] = $gid;
        $result = Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :gid");
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function delete(int $gid): int {
        $result = Database::delete(self::TABLE, self::PRIMARY_KEY . " = :gid", ['gid' => $gid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }
}
?>