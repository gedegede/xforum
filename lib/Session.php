<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

use Models\MemberModel;

class Session {
    private static bool $initialized = false;

    private static function init(bool $forceStart = false): void {
        if (self::$initialized) {
            return;
        }
        
        $config = require ROOT_PATH . '/config/app.php';
        session_name($config['cookie_prefix'] . 'sid');
        
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => $config['cookie_expire'],
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        $sessionCookieName = $config['cookie_prefix'] . 'sid';
        
        if ($forceStart || isset($_COOKIE[$sessionCookieName])) {
            session_start();
            self::$initialized = true;
        }
    }

    public static function start(): void {
        self::init(true);
    }

    public static function set(string $key, mixed $value): void {
        self::init(true);
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::init();
        if (!self::$initialized) {
            return $default;
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function delete(string $key): void {
        self::init();
        if (self::$initialized && isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function clear(): void {
        self::init();
        if (self::$initialized) {
            session_destroy();
            $_SESSION = [];
        }
    }

    public static function isLoggedIn(): bool {
        return !empty(self::getUid());
    }

    public static function getUid(): int {
        return (int)self::get('uid', 0);
    }

    public static function regenerateId(): void {
        self::init(true);
        if (self::$initialized) {
            session_regenerate_id(true);
        }
    }

    public static function getUser(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }
        return MemberModel::get(self::getUid());
    }
}
?>