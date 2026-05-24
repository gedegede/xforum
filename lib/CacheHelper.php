<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class CacheHelper {
    private const KEY_PREFIX = 'xforum';
    private const CACHE_TTL = 0;
    private const INDEX_KEY = '__index';

    private static array $memoryCache = [];
    private static ?string $namespace = null;

    public static function hasCache(string $tableName): bool {
        if (array_key_exists($tableName, self::$memoryCache)) {
            return true;
        }

        if (!self::isApcuAvailable() || !function_exists('apcu_exists')) {
            return false;
        }

        return apcu_exists(self::cacheKey($tableName));
    }

    public static function getCache(string $tableName): ?array {
        if (array_key_exists($tableName, self::$memoryCache)) {
            return self::$memoryCache[$tableName];
        }

        if (!self::isApcuAvailable()) {
            return null;
        }

        $success = false;
        $data = apcu_fetch(self::cacheKey($tableName), $success);
        if (!$success || !is_array($data)) {
            return null;
        }

        self::$memoryCache[$tableName] = $data;
        return $data;
    }

    public static function setCache(string $tableName, array $data): void {
        self::$memoryCache[$tableName] = $data;

        if (!self::isApcuAvailable()) {
            return;
        }

        apcu_store(self::cacheKey($tableName), $data, self::CACHE_TTL);
        self::rememberKey($tableName);
    }

    public static function deleteCache(string $tableName): void {
        unset(self::$memoryCache[$tableName]);

        if (!self::isApcuAvailable()) {
            return;
        }

        apcu_delete(self::cacheKey($tableName));
        self::forgetKey($tableName);
    }

    public static function clearAllCache(): void {
        self::$memoryCache = [];

        if (!self::isApcuAvailable()) {
            return;
        }

        foreach (self::getIndex() as $key) {
            apcu_delete($key);
        }

        apcu_delete(self::indexKey());
    }

    private static function rememberKey(string $tableName): void {
        $index = self::getIndex();
        $index[$tableName] = self::cacheKey($tableName);
        apcu_store(self::indexKey(), $index, self::CACHE_TTL);
    }

    private static function forgetKey(string $tableName): void {
        $index = self::getIndex();
        unset($index[$tableName]);
        apcu_store(self::indexKey(), $index, self::CACHE_TTL);
    }

    private static function getIndex(): array {
        $success = false;
        $index = apcu_fetch(self::indexKey(), $success);

        return $success && is_array($index) ? $index : [];
    }

    private static function cacheKey(string $tableName): string {
        return self::namespace() . $tableName;
    }

    private static function indexKey(): string {
        return self::namespace() . self::INDEX_KEY;
    }

    private static function namespace(): string {
        if (self::$namespace === null) {
            self::$namespace = self::KEY_PREFIX . ':' . md5(ROOT_PATH) . ':';
        }

        return self::$namespace;
    }

    private static function isApcuAvailable(): bool {
        if (!function_exists('apcu_fetch') || !function_exists('apcu_store') || !function_exists('apcu_delete')) {
            return false;
        }

        if (function_exists('apcu_enabled')) {
            return apcu_enabled();
        }

        return filter_var((string)ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}
?>
