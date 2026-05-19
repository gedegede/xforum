<?php
class Session {
    private static $initialized = false;

    private static function init($forceStart = false) {
        if (self::$initialized) {
            return;
        }
        
        $config = require ROOT_PATH . '/config/app.php';
        session_name($config['cookie_prefix'] . 'sid');
        session_set_cookie_params($config['cookie_expire'], '/');
        
        $sessionCookieName = $config['cookie_prefix'] . 'sid';
        
        if ($forceStart || isset($_COOKIE[$sessionCookieName])) {
            session_start();
            self::$initialized = true;
        }
    }

    public static function start() {
        self::init(true);
    }

    public static function set($key, $value) {
        self::init(true);
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::init();
        if (!self::$initialized) {
            return $default;
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function delete($key) {
        self::init();
        if (self::$initialized && isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function clear() {
        self::init();
        if (self::$initialized) {
            session_destroy();
            $_SESSION = [];
        }
    }

    public static function isLoggedIn() {
        return !empty(self::getUid());
    }

    public static function getUid() {
        return self::get('uid', 0);
    }

    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        return MemberModel::get(self::getUid());
    }
}
?>
