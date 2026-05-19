<?php
class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load($class) {
        $paths = [
            ROOT_PATH . '/lib/' . $class . '.php',
            ROOT_PATH . '/models/' . $class . '.php',
            ROOT_PATH . '/controllers/' . $class . '.php',
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require $path;
                return;
            }
        }
    }
}
