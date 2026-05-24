<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class CacheHelper {
    const CACHE_DIR = ROOT_PATH . '/cache';
    
    private static $memoryCache = [];

    public static function getCacheFilePath(string $tableName): string {
        return self::CACHE_DIR . '/' . $tableName . '.php';
    }

    public static function hasCache(string $tableName): bool {
        return file_exists(self::getCacheFilePath($tableName));
    }

    public static function getCache(string $tableName): ?array {
        if (isset(self::$memoryCache[$tableName])) {
            return self::$memoryCache[$tableName];
        }

        $filePath = self::getCacheFilePath($tableName);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = require $filePath;
        self::$memoryCache[$tableName] = $data;
        return $data;
    }

    public static function setCache(string $tableName, array $data): void {
        $filePath = self::getCacheFilePath($tableName);
        $content = "<?php\nreturn " . var_export($data, true) . ";\n";
        file_put_contents($filePath, $content);
        self::$memoryCache[$tableName] = $data;
        self::clearOpcache($filePath);
    }

    public static function deleteCache(string $tableName): void {
        $filePath = self::getCacheFilePath($tableName);
        if (file_exists($filePath)) {
            self::clearOpcache($filePath);
            unlink($filePath);
        }
        unset(self::$memoryCache[$tableName]);
    }

    public static function clearAllCache(): void {
        $files = glob(self::CACHE_DIR . '/*.php');
        if ($files) {
            foreach ($files as $file) {
                self::clearOpcache($file);
                unlink($file);
            }
        }
        self::$memoryCache = [];
    }

    private static function clearOpcache(string $filePath): void {
        if (!function_exists('opcache_invalidate')) {
            return;
        }

        if (!ini_get('opcache.enable')) {
            return;
        }

        if (opcache_is_script_cached($filePath)) {
            opcache_invalidate($filePath, true);
        }
    }
}
?>