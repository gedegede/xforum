<?php
declare(strict_types=1);

namespace Models;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Lib\Database;
use Lib\CacheHelper;

class SettingModel {
    const TABLE = 'next_setting';
    const PRIMARY_KEY = 'skey';

    public static function get(string $key, string $default = ''): string {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, string $value): void {
        $exists = Database::fetch("SELECT * FROM " . self::TABLE . " WHERE skey = :skey", ['skey' => $key]);
        if ($exists) {
            Database::query("UPDATE " . self::TABLE . " SET val = :val WHERE skey = :skey", ['val' => $value, 'skey' => $key]);
        } else {
            Database::query("INSERT INTO " . self::TABLE . " (skey, val) VALUES (:skey, :val)", ['skey' => $key, 'val' => $value]);
        }
        CacheHelper::deleteCache(self::TABLE);
    }

    public static function getAll(): array {
        $cache = CacheHelper::getCache(self::TABLE);
        if ($cache !== null) {
            return $cache;
        }

        $result = Database::fetchAll("SELECT * FROM " . self::TABLE);
        $settings = [];
        foreach ($result as $row) {
            $settings[$row['skey']] = $row['val'];
        }
        CacheHelper::setCache(self::TABLE, $settings);
        return $settings;
    }

    public static function getCollapsedFids(): array {
        $collapsedFids = self::get('collapsed_fids', '');
        if (empty($collapsedFids)) {
            return [];
        }
        return array_map('intval', explode(',', $collapsedFids));
    }

    public static function getReportForumFid(): int {
        return (int)self::get('report_forum_fid', '0');
    }

    public static function getApproveKeywords(): array {
        return self::parseKeywords(self::get('approve_keywords', ''));
    }

    public static function getBlockKeywords(): array {
        return self::parseKeywords(self::get('block_keywords', ''));
    }

    private static function parseKeywords(string $keywords): array {
        if (empty($keywords)) {
            return [];
        }
        return array_filter(array_map('trim', preg_split('/[\r\n,，]+/', $keywords)));
    }
}
?>
