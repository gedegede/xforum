<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Request {
    public static function getInt(string $key, int $default = 0): int {
        return isset($_GET[$key]) ? (int)$_GET[$key] : $default;
    }

    public static function postInt(string $key, int $default = 0): int {
        return isset($_POST[$key]) ? (int)$_POST[$key] : $default;
    }

    public static function int(string $key, int $default = 0): int {
        if (isset($_POST[$key])) {
            return (int)$_POST[$key];
        }
        return isset($_GET[$key]) ? (int)$_GET[$key] : $default;
    }

    public static function getString(string $key, string $default = ''): string {
        return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
    }

    public static function postString(string $key, string $default = ''): string {
        return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
    }

    public static function string(string $key, string $default = ''): string {
        if (isset($_POST[$key])) {
            return trim((string)$_POST[$key]);
        }
        return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
    }

    public static function getHtml(string $key, string $default = ''): string {
        if (!isset($_GET[$key])) {
            return $default;
        }
        return htmlspecialchars(trim((string)$_GET[$key]), ENT_QUOTES, 'UTF-8');
    }

    public static function postHtml(string $key, string $default = ''): string {
        if (!isset($_POST[$key])) {
            return $default;
        }
        return htmlspecialchars(trim((string)$_POST[$key]), ENT_QUOTES, 'UTF-8');
    }

    public static function getArray(string $key, array $default = []): array {
        return isset($_GET[$key]) && is_array($_GET[$key]) ? $_GET[$key] : $default;
    }

    public static function postArray(string $key, array $default = []): array {
        return isset($_POST[$key]) && is_array($_POST[$key]) ? $_POST[$key] : $default;
    }

    public static function getBool(string $key): bool {
        return isset($_GET[$key]) && $_GET[$key];
    }

    public static function postBool(string $key): bool {
        return isset($_POST[$key]) && $_POST[$key];
    }

    public static function getRaw(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    public static function postRaw(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public static function all(): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $_POST;
        }
        return $_GET;
    }

    public static function has(string $key): bool {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    public static function method(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function isPost(): bool {
        return self::method() === 'POST';
    }

    public static function isGet(): bool {
        return self::method() === 'GET';
    }
}
