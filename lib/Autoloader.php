<?php
declare(strict_types=1);

namespace Lib;

if (!defined('ROOT_PATH')) {
    exit('Access denied');
}

class Autoloader {
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load(string $class): void {
        if (strpos($class, '\\') === false) {
            return;
        }

        [$namespace, $className] = explode('\\', $class, 2);

        $pathMap = [
            'Lib' => ROOT_PATH . '/lib/',
            'Models' => ROOT_PATH . '/models/',
            'Controllers' => ROOT_PATH . '/controllers/',
        ];

        if (isset($pathMap[$namespace])) {
            $path = $pathMap[$namespace] . $className . '.php';
            if (file_exists($path)) {
                require $path;
            }
        }
    }
}
?>