<?php
class Template {
    private static $vars = [];
    private static $templatePath = '';

    public static function init($templatePath = '') {
        if (empty($templatePath)) {
            self::$templatePath = ROOT_PATH . '/templates';
        } else {
            self::$templatePath = $templatePath;
        }
    }

    public static function set($key, $value) {
        self::$vars[$key] = $value;
    }

    public static function setAll($vars) {
        self::$vars = array_merge(self::$vars, $vars);
    }

    public static function render($template, $layout = 'layout/base') {
        extract(self::$vars);
        ob_start();
        include self::$templatePath . '/' . $template . '.php';
        $content = ob_get_clean();
        
        if ($layout) {
            ob_start();
            include self::$templatePath . '/' . $layout . '.php';
            return ob_get_clean();
        }
        return $content;
    }

    public static function display($template) {
        echo self::render($template);
    }

    public static function clear() {
        self::$vars = [];
    }
}
