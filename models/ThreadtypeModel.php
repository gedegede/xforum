<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class ThreadtypeModel {
    const TABLE = 'next_threadtype';
    const PRIMARY_KEY = 'typeid';

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return array_values($cache);
        }

        $data = Database::fetchAll("SELECT * FROM " . self::TABLE . " ORDER BY typeid ASC");
        usort($data, static function (array $a, array $b): int {
            $fidCompare = (int)$a['fid'] <=> (int)$b['fid'];
            if ($fidCompare !== 0) {
                return $fidCompare;
            }

            $sortCompare = (int)$a['sort_order'] <=> (int)$b['sort_order'];
            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return (int)$a['typeid'] <=> (int)$b['typeid'];
        });
        $indexed = [];
        foreach ($data as $item) {
            $indexed[$item['typeid']] = $item;
        }
        CacheHelper::setCache(self::TABLE, $indexed);
        return array_values($indexed);
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

    public static function get(int $typeid): ?array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return $cache[$typeid] ?? null;
        }

        return Database::fetch("SELECT * FROM " . self::TABLE . " WHERE " . self::PRIMARY_KEY . " = :typeid", ['typeid' => $typeid]);
    }

    public static function getTypeName(int $typeid): string {
        $row = self::get($typeid);
        return $row ? $row['name'] : '';
    }

    public static function create(array $data): int {
        $id = Database::insert(self::TABLE, $data);
        CacheHelper::deleteCache(self::TABLE);
        return $id;
    }

    public static function update(int $typeid, array $data): int {
        $result = Database::update(self::TABLE, $data, self::PRIMARY_KEY . " = :typeid", ['typeid' => $typeid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }

    public static function delete(int $typeid): int {
        $result = Database::delete(self::TABLE, self::PRIMARY_KEY . " = :typeid", ['typeid' => $typeid]);
        CacheHelper::deleteCache(self::TABLE);
        return $result;
    }
}
?>
