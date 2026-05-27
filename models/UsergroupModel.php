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
    public const PERMISSION_KEYS = [
        'deny_access',
        'deny_thread',
        'thread_need_approve',
        'deny_reply',
        'post_need_approve',
        'deny_pm',
        'deny_search',
        'deny_edit',
        'deny_favorite',
        'deny_rate',
        'deny_report',
        'admin_thread',
        'admin_setting',
        'admin_forum',
        'admin_usergroup',
        'admin_user',
        'admin_log',
    ];

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return self::sortGroups(self::parseJsonData($cache));
        }

        $groups = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
        $indexed = [];
        foreach ($groups as $group) {
            $indexed[$group['gid']] = $group;
        }
        CacheHelper::setCache(self::TABLE, $indexed);
        return self::sortGroups(self::parseJsonData($indexed));
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

    private static function sortGroups(array $groups): array {
        $typeOrder = [
            'system' => 1,
            'special' => 2,
            'member' => 3,
        ];

        uasort($groups, function(array $left, array $right) use ($typeOrder): int {
            $leftTypeOrder = $typeOrder[$left['group_type'] ?? ''] ?? 4;
            $rightTypeOrder = $typeOrder[$right['group_type'] ?? ''] ?? 4;
            if ($leftTypeOrder !== $rightTypeOrder) {
                return $leftTypeOrder <=> $rightTypeOrder;
            }

            if (($left['group_type'] ?? '') === 'member' && ($right['group_type'] ?? '') === 'member') {
                $creditCompare = (int)($left['credit_lower'] ?? 0) <=> (int)($right['credit_lower'] ?? 0);
                if ($creditCompare !== 0) {
                    return $creditCompare;
                }
            }

            return (int)($left['gid'] ?? 0) <=> (int)($right['gid'] ?? 0);
        });

        return $groups;
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

    public static function hasPermission(int $gid, string $key): bool {
        $groups = self::getAll();
        $group = $groups[$gid] ?? null;
        return $group ? !empty($group[$key]) : false;
    }

    public static function canManage(int $gid): bool {
        return self::hasPermission($gid, 'admin_thread')
            || self::hasPermission($gid, 'admin_setting')
            || self::hasPermission($gid, 'admin_forum')
            || self::hasPermission($gid, 'admin_usergroup')
            || self::hasPermission($gid, 'admin_user')
            || self::hasPermission($gid, 'admin_log');
    }

    public static function threadNeedApprove(int $gid): bool {
        return self::hasPermission($gid, 'thread_need_approve');
    }

    public static function postNeedApprove(int $gid): bool {
        return self::hasPermission($gid, 'post_need_approve');
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

        $groups = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY gid ASC");
        foreach ($groups as $group) {
            if ($group['group_type'] == 'member') {
                return self::parseJsonDataItem($group);
            }
        }

        return null;
    }

    public static function getRegisterDefaultGroup(?int $configuredGid = null): ?array {
        $groups = self::getAll();

        if ($configuredGid !== null && $configuredGid > 0 && isset($groups[$configuredGid])) {
            $group = $groups[$configuredGid];
            if (self::canAssignOnRegister($group)) {
                return $group;
            }
        }

        if (isset($groups[2]) && self::canAssignOnRegister($groups[2])) {
            return $groups[2];
        }

        foreach ($groups as $group) {
            if (($group['group_type'] ?? '') === 'member' && self::canAssignOnRegister($group)) {
                return $group;
            }
        }

        foreach ($groups as $group) {
            if (self::canAssignOnRegister($group)) {
                return $group;
            }
        }

        return null;
    }

    public static function canAssignOnRegister(array $group): bool {
        return (int)($group['gid'] ?? 0) !== 1
            && empty($group['admin_thread'])
            && empty($group['admin_setting'])
            && empty($group['admin_forum'])
            && empty($group['admin_usergroup'])
            && empty($group['admin_user'])
            && empty($group['admin_log']);
    }

    public static function getMemberGroupByCredit(int $credit): ?array {
        $matched = null;
        $lowestMemberGroup = null;

        foreach (self::getAll() as $group) {
            if (($group['group_type'] ?? '') !== 'member') {
                continue;
            }

            if ($lowestMemberGroup === null) {
                $lowestMemberGroup = $group;
            }

            if ((int)($group['credit_lower'] ?? 0) <= $credit) {
                $matched = $group;
            }
        }

        return $matched ?? $lowestMemberGroup;
    }

    public static function create(array $data): int {
        if (!isset($data['json_data'])) {
            $data['json_data'] = '{}';
        }
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
