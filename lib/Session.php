<?php
class Session {
    private static $initialized = false;

    private static function init() {
        if (self::$initialized) {
            return;
        }
        $config = require ROOT_PATH . '/config/app.php';
        session_name($config['cookie_prefix'] . 'sid');
        session_set_cookie_params($config['cookie_expire'], '/');
        session_start();
        self::$initialized = true;
    }

    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::init();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function delete($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function clear() {
        self::init();
        session_destroy();
        $_SESSION = [];
    }

    public static function isLoggedIn() {
        self::init();
        return !empty(self::get('uid'));
    }

    public static function getUid() {
        self::init();
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